<?php

namespace App\Livewire;

use App\Actions\ToggleWishJoin;
use App\Actions\NotifyWishParticipants;
use App\Models\TripWish;
use Illuminate\View\View;
use Livewire\Component;

class WishJoinControl extends Component
{
    public int $wishId;

    public ?int $updatedAt = null;

    public bool $allowCancel = true;

    public bool $simpleJoinLabel = false;

    public bool $detailsOpen = false;

    public function openDetails(): void
    {
        abort_unless(auth()->check(), 403);
        $this->detailsOpen = true;
    }

    public function mount(int $wishId): void
    {
        $this->wishId = $wishId;
    }

    public function toggle(ToggleWishJoin $toggleWishJoin, NotifyWishParticipants $notifyWishParticipants): void
    {
        $wish = $this->wish();

        if (! $this->allowCancel && $wish->users->contains(auth()->id())) {
            $this->dispatch('wish-notice', wishId: $wish->id, message: '已完成報名');
            $this->dispatch('wish-join-updated', wishId: $wish->id, hasJoined: true, count: $wish->users()->count());

            return;
        }

        $status = $toggleWishJoin->handle($wish, auth()->user());

        if ($status === 'joined') {
            $notifyWishParticipants->handle($wish, auth()->user());
        }
        $hasJoined = $status !== 'canceled';
        $count = $wish->users()->count();

        $this->updatedAt = now()->timestamp;
        $this->dispatch('wish-notice', wishId: $wish->id, message: $status === 'canceled' ? '已取消 +1' : '已完成報名');
        $this->dispatch('wish-join-updated', wishId: $wish->id, hasJoined: $hasJoined, count: $count);
    }

    public function render(): View
    {
        $wish = $this->wish();

        return view('livewire.wish-join-control', [
            'wish' => $wish,
            'hostedCount' => $this->detailsOpen
                ? TripWish::where('user_id', $wish->user_id)->count()
                : null,
        ]);
    }

    private function wish(): TripWish
    {
        return TripWish::with(['user', 'users'])
            ->withCount('users')
            ->findOrFail($this->wishId);
    }
}
