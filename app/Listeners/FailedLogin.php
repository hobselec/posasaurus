<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Log;

class FailedLogin {

    public function handle(Failed $event)
    {

        $credentials = $event->credentials;
        $credentials['password'] = "*";

        Log::channel('access')->info('Error', ['credentials'=> $credentials]);

    }
}