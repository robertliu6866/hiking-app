<?php

namespace App\Livewire;

use App\Models\Trip;
use App\Models\TripWish;
use Illuminate\View\View;
use Livewire\Component;

class DashboardJoinedTrips extends Component
{
    public function render(): View
    {
        $user = auth()->user()->fresh();
        $status = $user->membership_status;

        $joinedTrips = auth()->user()
            ->joinedTrips()
            ->with('user')
            ->withCount('participants')
            ->upcoming()
            ->orderBy('departure_time')
            ->get();

        $recommendedTrips = Trip::query()
            ->with([
                'user' => fn ($query) => $query->withCount(['trips', 'joinedTrips']),
                'participants',
            ])
            ->withCount(['participants', 'pendingOrders'])
            ->upcoming()
            ->where('status', 'open')
            ->whereDoesntHave('participants', fn ($query) => $query->whereKey($user->id))
            ->orderBy('departure_time')
            ->get()
            ->sortByDesc(fn (Trip $trip) => $user->tripMatchScore($trip))
            ->take(3)
            ->values();

        $joinedWishes = TripWish::query()
            ->select('trip_wishes.*')
            ->with(['user', 'users'])
            ->withCount('users')
            ->join('trip_wish_user as my_wish_status', 'my_wish_status.trip_wish_id', '=', 'trip_wishes.id')
            ->where('my_wish_status.user_id', $user->id)
            ->where('my_wish_status.status', 'joined')
            ->orderByDesc('my_wish_status.updated_at')
            ->orderByDesc('trip_wishes.created_at')
            ->take(3)
            ->get();

        return view('livewire.dashboard-joined-trips', [
            'user' => $user,
            'statusLabel' => match ($status) {
                'trial' => '試用會員',
                'active' => '正式會員',
                'expired' => '已過期',
                default => '待付款',
            },
            'statusClasses' => match ($status) {
                'trial' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                'active' => 'border-sky-200 bg-sky-50 text-sky-700',
                'expired' => 'border-red-200 bg-red-50 text-red-700',
                default => 'border-amber-200 bg-amber-50 text-amber-700',
            },
            'joinedTrips' => $joinedTrips,
            'joinedWishes' => $joinedWishes,
            'recommendedTrips' => $recommendedTrips,
        ]);
    }
}
