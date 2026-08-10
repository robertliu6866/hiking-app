<?php

namespace App\Actions;

use App\Models\TripWish;
use App\Models\User;

class ToggleWishJoin
{
    public function handle(TripWish $wish, User $user): string
    {
        if ($wish->allUsers()->wherePivot('status', 'joined')->whereKey($user->id)->exists()) {
            $wish->allUsers()->updateExistingPivot($user->id, [
                'status' => 'canceled',
            ]);

            return 'canceled';
        }

        $wish->allUsers()->syncWithoutDetaching([
            $user->id => ['status' => 'joined'],
        ]);

        return 'joined';
    }
}
