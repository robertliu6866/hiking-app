<?php

namespace App\Livewire;

use App\Actions\NotifyFollowersAboutActivity;
use App\Actions\NotifyWishParticipants;
use App\Actions\ToggleWishJoin;
use App\Models\TripWish;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class HomepageWishButton extends Component
{
    public ?int $wishId = null;

    public string $mountain;

    public ?string $wishedDate = null;

    public ?string $note = null;

    public bool $joined = false;

    public int $count = 0;

    public string $variant = 'primary';

    public bool $showAvatars = false;

    public function mount(string $mountain, ?string $wishedDate = null, ?string $note = null, ?int $wishId = null, string $variant = 'primary', bool $showAvatars = false): void
    {
        $this->mountain = $mountain;
        $this->wishedDate = $wishedDate;
        $this->note = $note;
        $this->wishId = $wishId;
        $this->variant = $variant;
        $this->showAvatars = $showAvatars;

        $wish = $this->resolveWish();

        if ($wish) {
            $this->wishId = $wish->id;
            $this->joined = $wish->users()->whereKey(auth()->id())->exists();
            $this->count = $wish->users()->count();
        }
    }

    public function toggle(ToggleWishJoin $toggleWishJoin, NotifyFollowersAboutActivity $notifyFollowers, NotifyWishParticipants $notifyWishParticipants): void
    {
        $wish = $this->resolveWish();

        if (! $wish) {
            $wish = TripWish::create([
                'user_id' => auth()->id(),
                'mountain' => $this->mountain,
                'wished_date' => $this->wishedDate,
                'note' => $this->note,
            ]);

            $notifyFollowers->handle(auth()->user(), $wish);
            $wish->allUsers()->syncWithoutDetaching([
                auth()->id() => ['status' => 'joined'],
            ]);
            $status = 'joined';
        } else {
            $status = $toggleWishJoin->handle($wish, auth()->user());

            if ($status === 'joined') {
                $notifyWishParticipants->handle($wish, auth()->user());
            }
        }

        $this->wishId = $wish->id;
        $this->joined = $status !== 'canceled';
        $this->count = $wish->users()->count();

        $this->dispatch('homepage-wish-updated', mountain: $this->mountain, wishedDate: $this->wishedDate);
    }

    public function render(): View
    {
        return view('livewire.homepage-wish-button', [
            'joinedUsers' => $this->joinedUsers(),
        ]);
    }

    private function resolveWish(): ?TripWish
    {
        if ($this->wishId) {
            return TripWish::find($this->wishId);
        }

        return TripWish::where('mountain', $this->mountain)
            ->when($this->wishedDate, fn ($query) => $query->whereDate('wished_date', $this->wishedDate))
            ->when(! $this->wishedDate, fn ($query) => $query->whereNull('wished_date'))
            ->first();
    }

    private function joinedUsers(): Collection
    {
        $wish = $this->resolveWish();

        if (! $wish || ! $this->showAvatars) {
            return collect();
        }

        $wish->loadMissing('users');

        return $wish->users->unique('id')->values();
    }
}
