<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\User;
use Socialite;
use Utils;
use App\Services\ActivationService;
use App\Traits\Encryptable;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;

class SocialAuthController extends Controller
{
    use Encryptable;

    protected $activationService;

    public function __construct(ActivationService $activationService)
    {
        $this->activationService = $activationService;
    }

    public function redirect(Request $request, $from, $remember)
    {
            $request->session()->put('from', $from);
            $request->session()->put('remember', $remember);
            return Socialite::driver('facebook')->redirect();
    }

    public function facebookCallback(Request $request)
    {
        // when facebook call us a with token
        try
        {
            $from = $request->session()->get('from', 'default');
            $remember = $request->session()->get('remember');

            $facebookUser = Socialite::driver('facebook')->user();
            $response = $this->addUserFromSocial($facebookUser, $from, $remember, 'facebook');

            return view('social.social_window_close', ['response' => $response]);
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    public function googleRedirect(Request $request, $from, $remember)
    {
        $request->session()->put('from', $from);
        $request->session()->put('remember', $remember);
        return Socialite::driver('google')->redirect();
    }

    public function googleCallback(Request $request)
    {
        // when google call us a with token
        try
        {
            $from = $request->session()->get('from', 'default');
            $remember = $request->session()->get('remember');

            $googleUser = Socialite::driver('google')->user();
            $response = $this->addUserFromSocial($googleUser, $from, $remember, 'google');

            return view('social.social_window_close', ['response' => $response]);
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    public function addUserFromSocial($userInfo, $from, $remember, $social)
    {
        $email          = $userInfo->getEmail();
        $encryptedEmail = $this->my_encrypt($userInfo->getEmail());
        $response = [
            'token' => '',
            'user'  => '' ,
            'from'  => '',
            'message' => '',
            'error' => false
        ];
        if($from == 'signUp')
        {
            if (User::where("email", $encryptedEmail)->exists())
            {
                $response['message'] = 'User with email ' . $email . ' already exists';
                $response['error']   = true;
                return $response;
            }
            else
            {
                $user = new User;

                $user->name                     = $userInfo->getName();
                $user->user_name                = $email;
                $user->email                    = $email;
                $user->password                 = Hash::make(str_random());
                $user->password_last_changed_at = Carbon::now();
                $user->activated                = 1;
                $user->signup_source            = $social;
                $user->show_welcome_msg         = 1;
                $user->save();

                $response['from'] = 'signUp';
            }
        }
        else
        {
            if (!User::where("email", $encryptedEmail)->exists())
            {
                $response['message'] = 'User with email ' . $email . ' not exists Please Sign Up';
                $response['error']   = true;
                return $response;
            }
            else
            {
                $user = User::where('email', $encryptedEmail)
                      ->first();
                $response['from'] = 'login';
            }
        }

        //stay logged In to 30 days
        $customClaims = [];
        if($remember)
        {
            $expiryDate = Carbon::now()->addDays(30)->timestamp;
            $customClaims = ['exp' => $expiryDate];
        }

        // Log in user automatically and redirect him to Update Profile page
        $token     = JWTAuth::fromUser($user, $customClaims);
        $userArray = $user->toArray();
        $userArray['force_change_password'] = false;

        $response['token'] = $token;
        $response['user']  = $userArray;

        return $response;
    }
}
