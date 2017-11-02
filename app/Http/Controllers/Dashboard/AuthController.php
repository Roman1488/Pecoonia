<?php

namespace App\Http\Controllers\Dashboard;

use App;
use Auth;
use App\Admin;
use Validator;
use JsValidator;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    protected $redirectTo = '/dashboard';
    protected $guard = 'admin';

    public function loginForm()
    {
        if (Auth::guard($this->guard)->check()) {
            return redirect('/dashboard');
        }

        $validator = JsValidator::make([
            'username' => 'required|alpha|min:8',
            'password' => 'required|alpha_num|min:8',
        ]);

        return view('dashboard.login', [
            'validator' => $validator,
        ]);
    }

    public function login(Request $request)
    {
        $this->validateLogin($request);

        $throttles = $this->isUsingThrottlesLoginsTrait();

        if ($throttles && $lockedOut = $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        $credentials = $request->only('username', 'password');

        if (Auth::guard($this->getGuard())->attempt($credentials, $request->has('remember'))) {
            return $this->handleUserWasAuthenticated($request, $throttles);
        }

        if ($throttles && ! $lockedOut) {
            $this->incrementLoginAttempts($request);
        }

        return $this->sendFailedLoginResponse($request);
    }

    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            'username' => 'required|alpha|min:8',
            'password' => 'required|alpha_num|min:8',
        ]);
    }

    public function logout()
    {
        Auth::guard($this->guard)->logout();
        return redirect('/');
    }

    protected function createAdmin()
    {
        return Admin::create([
            'username' => 'administrator',
            'password' => bcrypt('H546zdfdxfg'),
        ]);
    }

    public function changePassForm()
    {
        $validator = JsValidator::make([
            'password_old' => 'required|alpha_num|min:8',
            'password' => 'required|confirmed|alpha_num|min:8',
            'password_confirmation' => 'required|alpha_num|min:8',
        ]);

        return view('dashboard.profile.profile', [
            'validator' => $validator,
        ]);
    }

    public function changePass(Request $request)
    {
        $credentials = $request->only('password_old', 'password', 'password_confirmation');

        $validator = Validator::make($credentials, [
            'password_old' => 'required|alpha_num|min:8',
            'password' => 'required|confirmed|alpha_num|min:8',
            'password_confirmation' => 'required|alpha_num|min:8',
        ]);

        if ($validator->fails()) {
            session()->flash('error', $validator->errors()->first());

            $this->throwValidationException(
                $request, $validator
            );
        }

        $user = Auth::guard('admin')->user();

        if (\Hash::check($credentials['password_old'], $user->password)) {
            $user->password = bcrypt($credentials['password']);

            if ($user->update()) {
                session()->flash('success', 'Your password has been changed successfully!');
            } else {
                session()->flash('error', 'Change password failed.');
            }
        } else {
            session()->flash('warning', 'You do not have necessary permissions to perform this action.');
        }

        return redirect()->back();
    }
}
