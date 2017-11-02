<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Validator;
use App\User;
use App\Libraries\Utils;
use Illuminate\Http\Request;
use Hash;
use Password;
use Carbon\Carbon;
use App\Traits\Encryptable;

class PasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;
    use Encryptable;

    /**
     * Create a new password controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Validate the request of sending reset link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function validateSendResetLinkEmail(Request $request)
    {
        $this->validate($request, ['email' => 'required|email']);

        if (isset($request['email']))
        {
            $request['email'] = $this->my_encrypt($request['email']);
        }
    }

    /**
     * Get the response for after the reset link has been successfully sent.
     *
     * @param  string  $response
     * @return string message
     */
    protected function getSendResetLinkEmailSuccessResponse($response)
    {
        return $this->success_state("Please check your email inbox.");
    }

    /**
     * Get the response for after the reset link could not be sent.
     *
     * @param  string  $response
     * @return string message
     */
    protected function getSendResetLinkEmailFailureResponse($response)
    {
        return $this->error(trans($response), '');
    }

    /**
     * Get the response for after a successful password reset.
     *
     * @param  string  $response
     * @return string message
     */
    protected function getResetSuccessResponse($response)
    {
        return $this->success_state("Password reset successful.");
    }

    /**
     * Get the response for after a failing password reset.
     *
     * @param  Request  $request
     * @param  string  $response
     * @return string message
     */
    protected function getResetFailureResponse(Request $request, $response)
    {
        return $this->error(trans($response), '');
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function reset(Request $request)
    {
        $this->validate(
            $request,
            $this->getResetValidationRules(),
            $this->getResetValidationMessages(),
            $this->getResetValidationCustomAttributes()
        );

        // Get current password of this email and check if new password is different from current.
        $encyptedEmail = $this->my_encrypt($request->email);

        $emailUser     = User::where('email', $encyptedEmail)->first();

        if (Hash::check($request->password, $emailUser->password))
                throw Utils::throwError('custom', 'New Password should be different from Current Password');

        $credentials          = $this->getResetCredentials($request);
        $credentials['email'] = $encyptedEmail;
        $broker               = $this->getBroker();

        $response = Password::broker($broker)->reset($credentials, function ($user, $password) {
            $this->resetPassword($user, $password);
        });

        switch ($response) {
            case Password::PASSWORD_RESET:
                $emailUser->password_last_changed_at = Carbon::now();
                $emailUser->save();
                return $this->getResetSuccessResponse($response);
            default:
                return $this->getResetFailureResponse($request, $response);
        }
    }
}
