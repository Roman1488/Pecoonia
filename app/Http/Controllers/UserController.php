<?php

namespace App\Http\Controllers;

use App\Libraries\Utils;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\HttpException;
use Carbon\Carbon;
use App\Traits\Encryptable;
use App\Cms;

use App\Services\ActivationService;

class UserController extends Controller
{
    use Encryptable;

    protected $activationService;

    public function __construct(ActivationService $activationService)
    {
        $this->activationService = $activationService;
    }

    /**
     * Returns the currently logged-in user.
     *
     * @return array
     * @throws \Exception
     */
    public function getUser()
    {
        try {
            $user = app('Dingo\Api\Auth\Auth')->user();
            if (!$user)
                throw Utils::throwError('not_logged_in');

            $userArray = $user->toArray();

            // Check if it's time to change password
            $userArray['force_change_password'] = $this->checkForcePasswordChange($userArray['password_last_changed_at']);

            return $this->success_item("Found logged in user", $userArray);
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Updates the currently logged-in user.
     *
     * @param Request $request
     *
     * @return mixed
     * @throws \Exception
     */
    public function updateUser(Request $request)
    {
        try {
            $user = app('Dingo\Api\Auth\Auth')->user();
            if (!$user)
                throw Utils::throwError('not_logged_in');

            $emailChanged = false;

            if ($email = $request->get("email")) {
                // Make sure email is valid
                if (filter_var($email, FILTER_VALIDATE_EMAIL) === false)
                    throw Utils::throwError("invalid_value", "Email");

                $inputEmail = $email;

                $email = $this->my_encrypt($request->get("email"));

                // Make sure email is unique
                if (mb_strtolower($inputEmail) != mb_strtolower($user->email))
                {
                    $emailChanged = true;
                    if (User::where("email", $email)->exists())
                        throw Utils::throwError("user_exists", $inputEmail);
                }
            }

            $currentPwd = $user->password;

            $user->fill($request->all());
            $user->timezone_code = $request->timezone_code;

            // If password provided, make sure it is not empty and is different than current password
            if ($request->get('change_password') === true) {
                $password = $request->get("password");
                if (strlen($password) < 1)
                    throw Utils::throwError("invalid_value", "Password");

                if (Hash::check($password, $currentPwd))
                    throw Utils::throwError('custom', 'New Password should be different from Current Password');

                $user->password                 = Hash::make($password);
                $user->password_last_changed_at = Carbon::now();
            }

            // If email has changed, send email confirmation email

            if ($emailChanged)
            {
                $user->activated = 0;
                $user->save();
                $this->activationService->sendActivationMail($user);
                $user->emailChanged = true;
            }
            else
            {
                $user->save();
            }

            return $this->success_item("User was updated successfully", $user);
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Deletes the currently logged-in user account if the password is correct.
     *
     * @return mixed
     * @throws \Exception
     */
    public function deleteUserAccount(Request $request)
    {
        try {
            $user = app('Dingo\Api\Auth\Auth')->user();

            if (!$user)
                throw Utils::throwError('not_logged_in');

            // Check password
            if (!$user->signup_source)
            {
                if (!$request->password)
                throw Utils::throwError('not_found', 'Password');

                $password = $request->password;

                if (!Hash::check($password, $user->password))
                    throw Utils::throwError('invalid_value', 'Password');
            }

            $user->delete();

            return $this->success_state("User deleted successfully");
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Creates a new user.
     *
     * @param Request $request
     *
     * @return mixed
     * @throws \Exception
     */
    public function createUser(Request $request)
    {
        try {
            $email = $request->get("email");
            $password = $request->get("password");

            // Make sure we at least get an Email and a Password for the new user
            // TODO: Apply password strength criteria
            if (!$email || filter_var($email, FILTER_VALIDATE_EMAIL) === false)
                throw Utils::throwError("invalid_value", "Email");
            if (!$password || strlen($password) < 1)
                throw Utils::throwError("invalid_value", "Password");

            $inputEmail = $email;

            $email = $this->my_encrypt($request->get("email"));

            // Validate that the email is unique
            if (User::where("email", $email)->exists())
                throw Utils::throwError("user_exists", $inputEmail);

            $user = new User;
            $user->fill($request->all());

            $user->timezone_code = $request->timezone_code;

            $user->password                 = Hash::make($password);
            $user->password_last_changed_at = Carbon::now();
            $user->show_welcome_msg         = 1;
            $user->save();

            // Send activation email
            $this->activationService->sendActivationMail($user);
            $cms = Cms::where('name', 'welcome_message')->first();
            return $this->success_item($cms->content, $user);
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Password change after some interval.
     *
     * @param Request $request
     *
     * @return mixed
     * @throws \Exception
     */
    public function forceChangePassword(Request $request)
    {
        try {
            $user = app('Dingo\Api\Auth\Auth')->user();
            if (!$user)
                throw Utils::throwError('not_logged_in');

            $currPassword = $request->currpwd;
            $newPassword  = $request->newpwd;

            if (!$currPassword || !$newPassword)
                throw Utils::throwError("invalid_value", "Password");

            // Validate that current password is correct
            if (!Hash::check($currPassword, $user->password))
                throw Utils::throwError('invalid_value', 'Current Password');

            // Validate that new password is different from current password
            if ($currPassword == trim($newPassword))
                throw Utils::throwError('custom', 'New Password should be different from Current Password');

            // Update data
            $user->password                 = Hash::make($newPassword);
            $user->password_last_changed_at = Carbon::now();

            $user->save();

            return $this->success_state("Password changed successfully.");
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Close the welcome message
     *
     * @return mixed
     * @throws \Exception
     */
    public function closeWelcomeMsg()
    {
        try {
            $user = app('Dingo\Api\Auth\Auth')->user();
            if (!$user)
                throw Utils::throwError('not_logged_in');

            // Update data
            $user->show_welcome_msg = 0;
            $user->save();

            return $this->success_state("success");
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }
}
