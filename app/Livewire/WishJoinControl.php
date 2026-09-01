<?php

namespace App\Livewire;

use App\Actions\ToggleWishJoin;
use App\Actions\NotifyWishParticipants;
use App\Models\TripWish;
use Illuminate\View\View;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class WishJoinControl extends Component
{
    public int $wishId;

    public ?int $updatedAt = null;

    public bool $allowCancel = true;

    public bool $simpleJoinLabel = false;

    #[Renderless]
    public function toggleHostVolunteer(): void
    {
        $wish = $this->wish();
        $userId = auth()->id();

        abort_unless($wish->users->contains($userId), 422, '請先表態同行，才能登記主揪。');

        $isVolunteer = (bool) optional($wish->users->firstWhere('id', $userId))->pivot?->willing_to_host;
        $wish->allUsers()->updateExistingPivot($userId, ['willing_to_host' => ! $isVolunteer]);

        $this->dispatch('wish-notice', wishId: $wish->id, message: $isVolunteer ? '已取消主揪登記' : '已登記願意主揪');
    }

    #[Renderless]
    public function drawHost(): void
    {
        $wish = $this->wish();

        abort_unless($wish->user_id === auth()->id(), 403);
        abort_if($wish->host_user_id, 422, '此團已有主揪。');

        $host = $wish->allUsers()
            ->wherePivot('status', 'joined')
            ->wherePivot('willing_to_host', true)
            ->inRandomOrder()
            ->first();

        abort_if(! $host, 422, '目前還沒有人自願主揪。');

        $wish->update(['host_user_id' => $host->id]);
        $this->dispatch('wish-notice', wishId: $wish->id, message: '已公開抽出主揪：'.$host->name);
    }

    public function mount(int $wishId): void
    {
        $this->wishId = $wishId;
    }

    #[Renderless]
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
        return view('livewire.wish-join-control', [
            'wish' => $this->wish(),
        ]);
    }

    private function wish(): TripWish
    {
        return TripWish::with(['user', 'host', 'users'])
            ->withCount('users')
            ->findOrFail($this->wishId);
    }
}
