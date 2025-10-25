<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class VerifyEmailQueued extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // Retry after 1min, 5min, 15min
    public $timeout = 30;

    public function __construct()
    {
        // Add a random delay between 3-10 seconds to spread out emails
        $this->delay = now()->addSeconds(rand(3, 10));
    }
}