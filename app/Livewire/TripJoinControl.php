<?php

namespace App\Livewire;

use App\Actions\ToggleTripJoin;
use App\Models\Trip;
use Illuminate\View\View;
use Livewire\Component;

class TripJoinControl extends Component
{
    public int $tripId;

    public string $variant = 'card';

    public ?int $updatedAt = null;

    public function mount(int $tripId, string $variant = 'card'): void
    {
        $this->tripId = $tripId;
        $this->variant = $variant;
    }

    public function toggle(ToggleTripJoin $toggleTripJoin): void
    {
        $trip = $this->trip();

        $status = $toggleTripJoin->handle($trip, auth()->user());
        $freshTrip = $this->trip();
        $participantsCount = $freshTrip->participants_count;
        $hasJoined = $freshTrip->participants->contains(auth()->id());

        $this->updatedAt = now()->timestamp;
        $this->dispatch('trip-notice', message: match ($status) {
            'canceled' => '已取消報名',
            'closed' => '這個活動目前沒有開放報名',
            'full' => '活動名額已滿',
            default => '已完成活動報名',
        });
        $this->dispatch(
            'trip-join-updated',
            tripId: $this->tripId,
            status: $status,
            hasJoined: $hasJoined,
            count: $participantsCount,
            isFull: $participantsCount >= $freshTrip->quota,
        );
    }

    public function openRegistration(): void
    {
        $this->dispatch('open-registration-form', tripId: $this->tripId);
    }

    public function render(): View
    {
        return view('livewire.trip-join-control', [
            'trip' => $this->trip(),
        ]);
    }

    private function trip(): Trip
    {
        return Trip::with([
            'user',
            'pendingOrders.user' => fn ($query) => $query
                ->withCount(['joinedTrips', 'trips']),
            'participants' => fn ($query) => $query
                ->withCount(['joinedTrips', 'trips'])
                ->orderBy('trip_user.created_at'),
            'registrations',
        ])
            ->withCount(['participants', 'pendingOrders'])
            ->findOrFail($this->tripId);
    }
}
