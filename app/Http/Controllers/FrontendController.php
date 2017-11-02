<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Traits\Encryptable;

class FrontendController extends Controller
{
    use Encryptable;

    function index()
    {
        return view('index.index');
    }

    /**
     * Check input for unique.
     *
     * @param Request $request
     *
     * @return mixed
     */
    function input(Request $request)
    {
        $request['user_name'] = $this->my_encrypt($request['user_name']);
        $request['email'] = $this->my_encrypt($request['email']);

        try {
            if ($request->has('password')) {
                $this->validatePassword($request);
            } else {
                $this->validate($request, [
                    'user_name' => 'unique:users',
                    'email' => 'unique:users',
                ]);
            }
        } catch (\Exception $e) {
            return $this->success_state("error");
        }
        return $this->success_state("valid");
    }

    /**
     * Check input for unique.
     *
     * @param Request $request
     *
     * @throws \Exception
     */
    function validatePassword($request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        if (!$user)
            throw new \Exception("User not found", 404);
        if (!Hash::check($request->get('password'), $user->getAuthPassword()))
            throw new \Exception("Password don`t match", 404);
    }
}
