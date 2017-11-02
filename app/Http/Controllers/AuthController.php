<?php

namespace App\Http\Controllers;


use App\User;
use App\Http\Requests;
use App\Exceptions\HttpException;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;
use App\Services\ActivationService;
use App\Traits\Encryptable;


class AuthController extends Controller
{
    use Encryptable;

    protected $activationService;

    public function __construct(ActivationService $activationService)
    {
        $this->activationService = $activationService;
    }

    /**
     * Authenticates a user and generates a token if auth succeeds. Use the token to make subsequent requests.
     *
     * @param Request $request
     *
     * @return array
     * @throws HttpException
     */
    public function authenticate(Request $request)
    {

        // Parameters will be passed through POST data
        // $credentials = $request->only(['email', 'password']);
        // Change authentication type from email -> username

        $credentials = $request->only(['user_name', 'password']);

        try {
            $credentials['user_name'] = $this->my_encrypt($credentials['user_name']);
            //stay logged In to 30 days
            $customClaims = [];
            if($request->remember)
            {
                $expiryDate = Carbon::now()->addDays(30)->timestamp;
                $customClaims = ['exp' => $expiryDate];
            }

            if (!$token = JWTAuth::attempt($credentials, $customClaims))
                throw new HttpException("Username and/or password is not correct. Please try again.", 401);

            // Get the user object
            // $user = User::where("email", $credentials['email'])->first();
            $user = User::where("user_name", $credentials['user_name'])->first();
            if (!$user)
                throw new \Exception("User not found", 404);

            // Check whether the user has confirmed his email
            if (!$user->activated)
            {
                throw new \Exception("In order to use Pecoonia you must verify your email address. Please check your email.", 401);
            }

            $userArray = $user->toArray();

            // Check if it's time to change password
            $userArray['force_change_password'] = false;
            if (!$user->signup_source)
            {
                $userArray['force_change_password'] = $this->checkForcePasswordChange($userArray['password_last_changed_at']);
            }
            // Successful authentication -- return the token
            return $this->success_item("Authentication ok - welcome to Pecoonia", [
                'token' => $token,
                'user' => $userArray
            ]);
        }
        catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Refreshes a token. Invalidates the current token, and returns a new one.
     *
     * @param Request $request
     *
     * @return array
     */
    public function refreshToken(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user)
                throw new \HttpException("Failed to authenticate", 401);

            $new_token = JWTAuth::parseToken()->refresh();

            return $this->success_item("Token refreshed successfully", ['new_token' => $new_token]);
        }
        catch (\Exception $error) {
            $this->error($error->getMessage(), $error->getCode());
        }
    }

    /**
     * Invalidates a token. Calling this route with a valid token is the same as logging out.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function endToken(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user)
                throw new \HttpException("Failed to authenticate", 401);

            // Simply refresh the token, but do not return the new one.
            JWTAuth::parseToken()->refresh();

            return $this->success_state("Token ended successfully");
        }
        catch (\Exception $error) {
            $this->error($error->getMessage(), $error->getCode());
        }
    }

    /**
    *   Activates the user account by confirming the email address.
    *
    *   @param string $token
    *
    *   @return mixed
    */
    public function activateUser($token)
    {
        if ($user = $this->activationService->activateUser($token)) {
            //auth()->login($user);
            return redirect('/#!/user-activated');
        }
        return redirect('/#!/404');
    }
}
