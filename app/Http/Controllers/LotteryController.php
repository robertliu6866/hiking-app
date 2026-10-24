<?php

namespace App\Http\Controllers;

use App\Models\TripWish;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class LotteryController extends Controller
{
    public function yushan(Request $request)
    {
        $supportsRouteMode = Schema::hasColumn('trip_wishes', 'route_mode');
        $minWishDate = Carbon::today()->addDays(5)->toDateString();
        $eligibleWishes = TripWish::query()
            ->whereNotNull('wished_date')
            ->whereDate('wished_date', '>=', $minWishDate);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'duration' => ['nullable', Rule::in(['single', 'overnight'])],
            'month' => ['nullable', 'date_format:Y-m'],
            'group' => ['nullable', Rule::in(['self', 'guided'])],
            'sort' => ['nullable', Rule::in(['date', 'popular'])],
        ]);
        $filters['q'] = trim($filters['q'] ?? '');
        $hasFilters = collect($filters)->except('sort')->filter()->isNotEmpty();
        $availableMonths = (clone $eligibleWishes)->orderBy('wished_date')
            ->pluck('wished_date')->map(fn ($date) => Carbon::parse($date)->format('Y-m'))
            ->unique()->values();
        $filteredWishes = clone $eligibleWishes;

        if ($filters['q'] !== '') {
            // Treat LIKE wildcards as literal characters in search terms.
            $keyword = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $filters['q']).'%';
            $filteredWishes->where(function ($query) use ($keyword) {
                $query->whereRaw("mountain LIKE ? ESCAPE '!'", [$keyword])
                    ->orWhereHas('user', fn ($user) => $user->whereRaw("name LIKE ? ESCAPE '!'", [$keyword]));
            });
        }
        if (! empty($filters['month'])) {
            $month = Carbon::createFromFormat('!Y-m', $filters['month']);
            $filteredWishes->whereDate('wished_date', '>=', $month->toDateString())
                ->whereDate('wished_date', '<=', $month->endOfMonth()->toDateString());
        }
        if (! empty($filters['duration'])) {
            if ($filters['duration'] === 'overnight') {
                $filteredWishes->where('guided_days', '>=', 2);
            } else {
                $filteredWishes->where('route_mode', 'single')
                    ->where(fn ($query) => $query->whereNull('guided_days')->orWhere('guided_days', '<=', 1));
            }
        }
        if (! empty($filters['group'])) {
            $filteredWishes->where('homepage_group', $filters['group']);
        }

        $wishQuery = (clone $filteredWishes)
            ->with(['user', 'users'])
            ->withCount('users');
        if (($filters['sort'] ?? 'date') === 'popular') {
            $wishQuery->orderByDesc('users_count');
        }
        $wishes = $wishQuery
            ->orderBy('wished_date')
            ->orderByDesc('users_count')
            ->orderByDesc('created_at')
            ->paginate(5)
            ->withQueryString();

        $summaryWishes = (clone $filteredWishes)
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
            'filters' => $filters,
            'hasFilters' => $hasFilters,
            'availableMonths' => $availableMonths,
        ]);
    }
}
