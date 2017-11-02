<?php

namespace App\Http\Controllers\Dashboard;

use Auth;
use App\User;
use Validator;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function viewUsers()
    {
        $users = User::all();

        return view('dashboard.users-table.table', [
            'users' => $users->toArray(),
        ]);
    }

    public function editUser(Request $request)
    {
        $id = $request->input('id');
        $credentials = $request->only('id', 'user_name', 'email');

        $validator = Validator::make($credentials, [
            'id'        => 'exists:users,id',
            'user_name' => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:users,email,'.$id,
        ]);

        if ($validator->fails()) {
            session()->flash('error', $validator->errors()->first());

            $this->throwValidationException(
                $request, $validator
            );
        }

        if (User::where('id', $id)->update($credentials)) {
            session()->flash('success', 'The user data were changed.');
        } else {
            session()->flash('error', 'An error occured when changing the user data.');
        }

        return redirect()->back();
    }

    public function deleteUserFirst(Request $request)
    {
        $credentials = $request->only('id');

        $validator = Validator::make($credentials, [
            'id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            session()->flash('error', $validator->errors()->first());

            $this->throwValidationException(
                $request, $validator
            );
        }

        $user = User::where('id', $credentials['id'])->first();
        $user->deleted_at = Date('Y-m-d H:i:s');

        if ($user->update()) {
            session()->flash('success', 'First deletion of the user is successful.');
        } else {
            session()->flash('error', 'An error occured upon first deletion of the user.');
        }

        return redirect()->back();
    }

    public function deleteUser(Request $request)
    {
        $id = $request->input('id');
        $password = $request->input('password');

        $validator = Validator::make([$id], [
            'id' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            session()->flash('error', $validator->errors()->first());

            $this->throwValidationException(
                $request, $validator
            );
        }

        $user = Auth::guard('admin')->user();

        if (\Hash::check($password, $user->password)) {
            if (User::destroy($id)) {
                session()->flash('success', 'The user and the data associated with them were deleted.');
            } else {
                session()->flash('error', 'An error occured when deleting a user.');
            }
        } else {
            session()->flash('warning', 'You do not have necessary permissions to perform this action.');
        }

        return redirect()->back();
    }
}
