<?php

namespace App\Support;

use App\Models\TripWish;

class WishCostSummary
{
    public static function for(TripWish $wish): array
    {
        $hostExempt = $wish->homepage_group === 'self' && $wish->route_mode === 'single';
        $people = $wish->expected_participants;
        $sharingCount = $people >= 2 ? $people - ($hostExempt ? 1 : 0) : null;
        $transport = (int) config('wishes.transport_fee');
        $guide = (int) config('wishes.guide_fee_per_person');
        $share = $sharingCount ? (int) ceil($transport / $sharingCount) : null;

        return [
            'guide' => $guide, 'transport' => $transport,
            'sharingCount' => $sharingCount, 'hostExempt' => $hostExempt,
            'share' => $share, 'total' => $share === null ? null : $guide + $share,
        ];
    }
}
