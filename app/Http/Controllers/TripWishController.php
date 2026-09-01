<?php

namespace App\Http\Controllers;

use App\Actions\NotifyFollowersAboutActivity;
use App\Actions\NotifyWishParticipants;
use App\Actions\ToggleWishJoin;
use App\Models\TripWish;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class TripWishController extends Controller
{
    public function store(Request $request)
    {
        $supportsRouteMode = Schema::hasColumn('trip_wishes', 'route_mode');
        $supportsGuidedCosts = Schema::hasColumn('trip_wishes', 'guided_days');
        $minWishDate = Carbon::today()->addDays(5)->toDateString();
        $redirectTo = $request->input('redirect_to');
        $requiresFutureWishDate = is_string($redirectTo)
            && str_starts_with($redirectTo, route('lotteries.yushan'));

        $validated = $request->validate([
            'mountain' => ['required', 'string', 'max:255'],
            'wished_date' => array_values(array_filter([
                'nullable',
                'date',
                $requiresFutureWishDate ? 'after_or_equal:'.$minWishDate : null,
            ])),
            'route_mode' => ['nullable', 'in:single,traverse,custom'],
            'note' => ['nullable', 'string', 'max:500'],
            'homepage_group' => ['nullable', 'in:guided,self'],
            'guided_days' => ['nullable', 'integer', 'min:1', 'max:14', 'required_if:homepage_group,guided'],
            'expected_participants' => ['nullable', 'integer', 'min:2', 'max:30', 'required_if:homepage_group,guided,self'],
            'redirect_to' => ['nullable', 'url'],
        ]);

        $wishQuery = TripWish::where('mountain', $validated['mountain']);

        if (isset($validated['wished_date'])) {
            $wishQuery->whereDate('wished_date', $validated['wished_date']);
        } else {
            $wishQuery->whereNull('wished_date');
        }

        if ($supportsRouteMode) {
            if (array_key_exists('route_mode', $validated)) {
                $wishQuery->where('route_mode', $validated['route_mode']);
            } else {
                $wishQuery->whereNull('route_mode');
            }
        }

        if (array_key_exists('homepage_group', $validated)) {
            $wishQuery->where('homepage_group', $validated['homepage_group']);
        } else {
            $wishQuery->whereNull('homepage_group');
        }

        $wish = $wishQuery->first();

        if (! $wish) {
            $wish = TripWish::create([
                'user_id' => $request->user()->id,
                'mountain' => $validated['mountain'],
                'wished_date' => $validated['wished_date'] ?? null,
                'note' => $validated['note'] ?? null,
                'homepage_group' => $validated['homepage_group'] ?? null,
            ]);

            if ($supportsRouteMode) {
                $wish->route_mode = $validated['route_mode'] ?? null;
                $wish->save();
            }

            if ($supportsGuidedCosts && in_array(($validated['homepage_group'] ?? null), ['guided', 'self'], true)) {
                $wish->guided_days = ($validated['homepage_group'] ?? null) === 'guided' ? $validated['guided_days'] : null;
                $wish->expected_participants = $validated['expected_participants'];
                $wish->save();
            }

            app(NotifyFollowersAboutActivity::class)->handle($request->user(), $wish);
        }

        $wasJoined = $wish->users()->whereKey($request->user()->id)->exists();

        $wish->allUsers()->syncWithoutDetaching([
            $request->user()->id => ['status' => 'joined'],
        ]);

        if (! $wish->wasRecentlyCreated && ! $wasJoined) {
            app(NotifyWishParticipants::class)->handle($wish, $request->user());
        }

        if ($request->expectsJson()) {
            return response()->noContent();
        }

        $redirectTo = $validated['redirect_to'] ?? null;

        if (is_string($redirectTo) && str_starts_with($redirectTo, url('/'))) {
            return redirect($redirectTo)->with('status', $wish->wasRecentlyCreated ? 'wish-created' : 'wish-existing');
        }

        return back()->with('status', $wish->wasRecentlyCreated ? 'wish-created' : 'wish-existing');
    }

    public function join(Request $request, TripWish $tripWish, ToggleWishJoin $toggleWishJoin, NotifyWishParticipants $notifyWishParticipants)
    {
        $validated = $request->validate([
            'redirect_to' => ['nullable', 'url'],
        ]);

        $status = $toggleWishJoin->handle($tripWish, $request->user());

        if ($status === 'joined') {
            $notifyWishParticipants->handle($tripWish, $request->user());
        }

        if ($request->expectsJson()) {
            return response()->noContent();
        }

        $redirectTo = $validated['redirect_to'] ?? null;

        if (is_string($redirectTo) && str_starts_with($redirectTo, url('/'))) {
            return redirect($redirectTo)->with('status', $status === 'canceled' ? 'wish-canceled' : 'wish-joined');
        }

        return back()->with('status', $status === 'canceled' ? 'wish-canceled' : 'wish-joined');
    }
}
