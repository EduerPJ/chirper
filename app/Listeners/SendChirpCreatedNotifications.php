<?php

namespace App\Listeners;

use App\Events\ChirpCreated;
use App\Models\User;
use App\Notifications\NewChirp;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendChirpCreatedNotifications implements ShouldQueue
{
    public function handle(ChirpCreated $event): void
    {
        foreach (User::whereNot('id', $event->chirp->user_id)->cursor() as $user) { // @phpstan-ignore-line
            $user->notify(new NewChirp($event->chirp));
        }
    }
}
