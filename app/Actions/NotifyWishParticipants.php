<?php

namespace App\Actions;

use App\Models\TripWish;
use App\Models\User;
use App\Notifications\WishParticipantJoined;
use Illuminate\Support\Facades\Notification;

class NotifyWishParticipants
{
    public function handle(TripWish $wish, User $joiningUser): void
    {
        $wish->loadMissing(['user', 'users']);

        $recipients = collect([$wish->user])
            ->merge($wish->users)
            ->filter()
            ->unique('id')
            ->reject(fn (User $user) => $user->is($joiningUser))
            ->filter(fn (User $user) => filled($user->line_user_id));

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send($recipients, new WishParticipantJoined($wish, $joiningUser));
    }
}
