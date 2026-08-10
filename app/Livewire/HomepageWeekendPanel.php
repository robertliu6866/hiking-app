<?php

namespace App\Livewire;

use App\Actions\NotifyFollowersAboutActivity;
use App\Actions\ToggleWishJoin;
use App\Models\TripWish;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class HomepageWeekendPanel extends Component
{
    public string $selectedPeak;

    public string $dateKey;

    public string $weekdayLabel;

    public string $displayDate;

    public int $guideThreshold = 20;

    public int $refreshKey = 0;

    public function mount(string $selectedPeak, string $dateKey, string $weekdayLabel, string $displayDate, int $guideThreshold = 20): void
    {
        $this->selectedPeak = $selectedPeak;
        $this->dateKey = $dateKey;
        $this->weekdayLabel = $weekdayLabel;
        $this->displayDate = $displayDate;
        $this->guideThreshold = $guideThreshold;
    }

    public function toggleWish(?int $wishId = null, bool $createNew = false): void
    {
        abort_unless(auth()->check(), 403);

        $toggleWishJoin = app(ToggleWishJoin::class);
        $notifyFollowers = app(NotifyFollowersAboutActivity::class);
        $wish = $createNew ? null : $this->resolveWishForToggle($wishId);

        if (! $wish) {
            $wish = TripWish::create([
                'user_id' => auth()->id(),
                'mountain' => $this->selectedPeak,
                'wished_date' => $this->dateKey,
                'note' => '首頁週末許願',
            ]);

            $notifyFollowers->handle(auth()->user(), $wish);
            $wish->allUsers()->syncWithoutDetaching([
                auth()->id() => ['status' => 'joined'],
            ]);

            $this->refreshKey++;
            $this->dispatch('homepage-wish-notice', message: '已加入這個週末的許願');

            return;
        }

        $status = $toggleWishJoin->handle($wish, auth()->user());
        $this->refreshKey++;
        $this->dispatch('homepage-wish-notice', message: $status === 'canceled' ? '已取消 +1' : '已加入這個週末的許願');
    }

    public function createWish(string $homepageGroup): void
    {
        abort_unless(auth()->check(), 403);
        abort_unless(in_array($homepageGroup, ['guided', 'self'], true), 422);

        $wish = TripWish::create([
            'user_id' => auth()->id(),
            'mountain' => $this->selectedPeak,
            'wished_date' => $this->dateKey,
            'note' => '首頁週末許願',
            'homepage_group' => $homepageGroup,
        ]);

        app(NotifyFollowersAboutActivity::class)->handle(auth()->user(), $wish);
        $wish->allUsers()->syncWithoutDetaching([
            auth()->id() => ['status' => 'joined'],
        ]);

        $this->refreshKey++;
        $this->dispatch('homepage-wish-notice', message: '已加入這個週末的許願');
    }

    public function refreshPanel(): void
    {
        $this->refreshKey++;
    }

    #[On('homepage-peak-selected')]
    public function selectPeak(string $selectedPeak): void
    {
        $peakNames = collect(config('baiyue.peaks'))->pluck('name');

        if (! $peakNames->contains($selectedPeak)) {
            return;
        }

        $this->selectedPeak = $selectedPeak;
        $this->refreshKey++;
    }


    public function render(): View
    {
        $dayWishes = $this->dayWishes();
        $dayWishes->each(fn (TripWish $wish) => $wish->setAttribute('has_homepage_guide', $this->hasGuide($wish)));
        $guidedWishes = $dayWishes->filter(fn (TripWish $wish) => $wish->has_homepage_guide)->values();
        $selfOrganizedWishes = $dayWishes->reject(fn (TripWish $wish) => $wish->has_homepage_guide)->values();

        return view('livewire.homepage-weekend-panel', [
            'dayWishes' => $dayWishes,
            'guidedWishes' => $guidedWishes,
            'selfOrganizedWishes' => $selfOrganizedWishes,
        ]);
    }

    private function dayWishes(): Collection
    {
        if (! Schema::hasTable('trip_wishes')) {
            return collect();
        }

        return TripWish::with([
            'user' => fn ($query) => $query->withCount(['trips', 'joinedTrips']),
            'users' => fn ($query) => $query->withCount(['trips', 'joinedTrips']),
        ])
            ->withCount(['users', 'allUsers'])
            ->where('mountain', $this->selectedPeak)
            ->whereDate('wished_date', $this->dateKey)
            ->orderByDesc('users_count')
            ->latest()
            ->get()
            ->each(function (TripWish $wish) {
                $wish->setAttribute(
                    'active_people_count',
                    $wish->users_count > 0 ? $wish->users_count : ($wish->all_users_count === 0 ? 1 : 0)
                );
                $participants = $wish->users;

                if ($wish->all_users_count === 0 && $wish->user) {
                    $participants = collect([$wish->user])->merge($participants);
                }

                $wish->setRelation('participantsForHomepage', $participants->unique('id')->values());
            })
            ->filter(fn (TripWish $wish) => $wish->active_people_count > 0)
            ->values();
    }

    private function hasGuide(TripWish $wish): bool
    {
        if (filled($wish->homepage_group)) {
            return $wish->homepage_group === 'guided';
        }

        $ownerCount = ($wish->user?->trips_count ?? 0) + ($wish->user?->joined_trips_count ?? 0);

        return $ownerCount >= $this->guideThreshold
            || $wish->users->contains(function ($user) {
                return (($user->trips_count ?? 0) + ($user->joined_trips_count ?? 0)) >= $this->guideThreshold;
            });
    }

    private function resolveWishForToggle(?int $wishId): ?TripWish
    {
        if ($wishId) {
            return TripWish::findOrFail($wishId);
        }

        $joinedWish = TripWish::where('mountain', $this->selectedPeak)
            ->whereDate('wished_date', $this->dateKey)
            ->whereHas('allUsers', function ($query) {
                $query->where('users.id', auth()->id())
                    ->where('trip_wish_user.status', 'joined');
            })
            ->first();

        if ($joinedWish) {
            return $joinedWish;
        }

        return TripWish::where('mountain', $this->selectedPeak)
            ->whereDate('wished_date', $this->dateKey)
            ->first();
    }

    private function hasJoined(TripWish $wish): bool
    {
        return auth()->check() && $wish->users->contains(auth()->id());
    }
}
