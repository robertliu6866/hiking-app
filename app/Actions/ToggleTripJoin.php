<?php

namespace App\Actions;

use App\Models\Trip;
use App\Models\User;

class ToggleTripJoin
{
    public function handle(Trip $trip, User $user): string
    {
        if ($trip->participants()->whereKey($user->id)->exists()) {
            $trip->participants()->detach($user->id);

            return 'canceled';
        }

        if ($trip->status !== 'open') {
            return 'closed';
        }

        if ($trip->participants()->count() >= $trip->quota) {
            return 'full';
        }

        $trip->participants()->attach($user->id);

        return 'joined';
    }
}
