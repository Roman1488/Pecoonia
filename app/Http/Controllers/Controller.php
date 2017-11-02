<?php

namespace App\Http\Controllers;

use Mail;
use App\Cms;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;
use Dingo\Api\Routing\Helpers;
use Carbon\Carbon;

class Controller extends BaseController
{
    use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests, Helpers;

    const FORCE_PASSWORD_CHANGE_DAYS = 90; // 90 days

    public function success_item($message, $item, $header = [], $item_type = false)
    {
        return array_merge([
            "message" => $message,
            "status" => "ok",
        ], $header, [
            "item" => is_object($item) ? (method_exists($item, "toArray") ? $item->toArray() : $item) : $item,
        ]);
    }

    public function success_state($message, $code = 200)
    {
        return $this->response->array([
            "message" => $message,
            "status" => "ok",
        ])->setStatusCode($code);
    }

    public function error($message, $code)
    {
        if ($code == 0 || !$code)
            $code = 400;

        return $this->response->array([
            "message" => $message,
            "status" => "error"
        ])->setStatusCode($code);
    }

    public function checkForcePasswordChange($password_last_changed_at)
    {
        $pwdLastChanged = ($password_last_changed_at) ? $password_last_changed_at : '2016-01-01';
        $pwdLastChanged = Carbon::parse($pwdLastChanged);
        $now            = Carbon::now();

        return ( $now->diffInDays($pwdLastChanged) >= self::FORCE_PASSWORD_CHANGE_DAYS );
    }

    public function sendFeedback (Request $request) {

        $data = $request->all();

        $name = '';
        $email = '';
        $subject = '';
        $message = '';

        foreach ($data as $value) {
            switch ($value['name']) {
                case 'name':
                    $name = $value['value'];
                    break;

                case 'email':
                    $email = $value['value'];
                    break;

                case 'subject':
                    $subject = $value['value'];
                    break;

                case 'message':
                    $message = $value['value'];
                    break;
            }
        }

        Mail::send('emails.feedback', [
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'bodyMessage' => $message
        ], function ($mail) use ($email) {
            $mail->from($email , config('mail.from.name'));
            $mail->to('info@pecoonia.com')->subject('Pecooina - Feedback');
        });

        // check for failures
        if (Mail::failures()) {
            return json_encode(['status'=> 'false', 'message' => Mail::failures()]);
        }

        return json_encode(['status'=> 'ok']);
    }

    public function getCmsContent()
    {
        return json_encode([
                            'status'   => 'ok',
                            'message'  => 'all cms fields are fetched',
                            'response' => Cms::get()->keyBy('name')->toArray()
                        ]);
    }
}
