<?php

namespace App\Services;

use Mail;
use App\UserActivation;
use App\User;

class ActivationService
{
    protected $userActivationObj;

    protected $resendAfter = 24;

    public function __construct()
    {
        $this->userActivationObj = new UserActivation;
    }

    public function sendActivationMail($user)
    {
        //if ($user->activated || !$this->shouldSend($user)) {

        if ($user->activated) {
            return;
        }

        $token = $this->userActivationObj->createActivation($user);

        $link = route('user.activate', $token);

        Mail::send('emails.account_activation', [
            'username' => $user->user_name,
            'link'     => $link
        ], function ($mail) use ($user) {
            $mail->from(config('mail.from.address'), config('mail.from.name'));
            $mail->to($user->email, $user->name)->subject('Pecooina - Account Activation');
        });
    }

    public function activateUser($token)
    {
        $activation = $this->userActivationObj->getActivationByToken($token);

        if ($activation === null) {
            return null;
        }

        $user = User::find($activation->user_id);

        $user->activated = 1;

        $user->save();

        $this->userActivationObj->deleteActivation($token);

        return $user;
    }

    private function shouldSend($user)
    {
        $activation = $this->userActivationObj->getActivation($user);
        return $activation === null || strtotime($activation->created_at) + 60 * 60 * $this->resendAfter < time();
    }
}