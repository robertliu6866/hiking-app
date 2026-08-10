<?php

namespace App\Actions;

use App\Models\Trip;
use App\Models\TripWish;
use App\Models\User;
use App\Notifications\FollowedUserActivityCreated;
use Illuminate\Support\Facades\Notification;

class NotifyFollowersAboutActivity
{
    public function handle(User $actor, Trip|TripWish $activity): void
    {
        $followers = $actor->followers()
            ->whereKeyNot($actor->id)
            ->get();

        if ($followers->isEmpty()) {
            return;
        }

        Notification::send($followers, new FollowedUserActivityCreated($actor, $activity));
    }
}
