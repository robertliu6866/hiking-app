<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\TripWish;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class LotteryController extends Controller
{
    public function yushan(Request $request)
    {
        $supportsRouteMode = Schema::hasColumn('trip_wishes', 'route_mode');
        $minWishDate = Carbon::today()->addDays(5)->toDateString();
        $eligibleWishes = TripWish::query()
            ->whereNotNull('wished_date')
            ->whereDate('wished_date', '>=', $minWishDate);

        $wishes = (clone $eligibleWishes)
            ->with(['user', 'users'])
            ->withCount('users')
            ->orderBy('wished_date')
            ->orderByDesc('users_count')
            ->orderByDesc('created_at')
            ->paginate(5)
            ->withQueryString();

        $summaryWishes = (clone $eligibleWishes)
            ->withCount('users')
            ->get();

        $mountainSuggestions = collect([
            '玉山主峰',
            '雪山主峰',
            '嘉明湖',
            '奇萊南華',
            '合歡群峰',
            '南湖大山',
            '北大武山',
            '大霸尖山',
            '能高安東軍',
            '桃山喀拉業',
            '屏風山',
            '郡大山',
        ])
            ->merge(TripWish::query()->whereNotNull('mountain')->pluck('mountain'))
            ->merge(Trip::query()->whereNotNull('mountain')->pluck('mountain'))
            ->map(fn ($mountain) => trim((string) $mountain))
            ->filter()
            ->unique()
            ->values();

        return view('lotteries.yushan', [
            'wishes' => $wishes,
            'totalParticipants' => $summaryWishes->sum('users_count'),
            'totalMountains' => $summaryWishes->count(),
            'nextWishDate' => $summaryWishes->sortBy('wished_date')->first()?->wished_date,
            'mountainSuggestions' => $mountainSuggestions,
            'minWishDate' => $minWishDate,
            'supportsRouteMode' => $supportsRouteMode,
        ]);
    }
}
