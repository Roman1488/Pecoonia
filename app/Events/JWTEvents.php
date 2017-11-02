<?php

namespace App\Events;

use App\Events\Event;
use Carbon\Carbon;
use App\Libraries\Utils;
use App\Exceptions\HttpException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Tymon\JWTAuth\Facades\JWTAuth;

class JWTEvents extends Event
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }

    public function valid()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user)
                throw Utils::throwError('not_logged_in');

            $user->last_login = Carbon::now(\Config('app.timezone'));
            $user->save();
        } catch (HttpException $e) {
            throw Utils::throwError('server_error');
        }
    }

}
