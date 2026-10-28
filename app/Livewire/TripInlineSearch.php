<?php

namespace App\Livewire;

use App\Models\TripWish;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Session;
use Livewire\Component;

class TripInlineSearch extends Component
{
    public string $search = '';

    #[Locked]
    public array $filters = [];

    #[Session(key: 'trip-inline-recent-searches')]
    public array $recentSearches = [];

    public function mount(): void
    {
        abort_unless(auth()->check(), 403);
    }

    #[On('trip-inline-filters')]
    public function updatePreferences(string $group = '', string $sort = 'date'): void
    {
        $values = validator(compact('group', 'sort'), [
            'group' => ['nullable', Rule::in(['self', 'guided'])],
            'sort' => ['required', Rule::in(['date', 'popular'])],
        ])->validate();
        $this->filters = array_merge($this->filters, $values);
        unset($this->results);
    }

    #[Computed]
    public function results()
    {
        $term = trim($this->search);
        if (mb_strlen($term) < 2 || mb_strlen($term) > 100) {
            return collect();
        }

        $keyword = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term).'%';
        $query = TripWish::query()->with('user')->withCount('users')
            ->whereDate('wished_date', '>=', today()->addDays(5))
            ->where(fn ($query) => $query->whereRaw("mountain LIKE ? ESCAPE '!'", [$keyword])
                ->orWhereRaw("note LIKE ? ESCAPE '!'", [$keyword])
                ->orWhereHas('user', fn ($user) => $user->whereRaw("name LIKE ? ESCAPE '!'", [$keyword])));

        if ($month = $this->filters['month'] ?? null) {
            $query->whereDate('wished_date', '>=', $month.'-01')
                ->whereDate('wished_date', '<=', Carbon::parse($month.'-01')->endOfMonth());
        }
        if (($this->filters['duration'] ?? '') === 'overnight') {
            $query->where('guided_days', '>=', 2);
        } elseif (($this->filters['duration'] ?? '') === 'single') {
            $query->where('route_mode', 'single')
                ->where(fn ($query) => $query->whereNull('guided_days')->orWhere('guided_days', '<=', 1));
        }
        if ($group = $this->filters['group'] ?? null) {
            $query->where('homepage_group', $group);
        }
        if (($this->filters['sort'] ?? '') === 'popular') {
            $query->orderByDesc('users_count');
        }

        return $query->orderBy('wished_date')->orderByDesc('id')->limit(10)->get();
    }

    public function highlight(string $text): string
    {
        $term = trim($this->search);
        if (mb_strlen($term) < 2 || mb_strlen($term) > 100) {
            return e($text);
        }
        // Split before escaping so HTML entities cannot become partial matches.
        $parts = preg_split('/('.preg_quote($term, '/').')/iu', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        return collect($parts)->map(fn ($part, $index) => $index % 2
            ? '<mark class="rounded bg-amber-100 px-0.5 text-inherit">'.e($part).'</mark>'
            : e($part))->implode('');
    }

    public function snippet(?string $note): string
    {
        $text = strip_tags($note ?? '');

        return Str::excerpt($text, trim($this->search), ['radius' => 45]) ?? Str::limit($text, 90);
    }

    public function useRecent(string $term): void
    {
        $this->search = mb_substr($term, 0, 100);
    }

    public function clearRecent(): void
    {
        $this->recentSearches = [];
    }

    public function choose(int $id)
    {
        abort_unless(auth()->check(), 403);
        abort_unless($this->results->contains('id', $id), 404);
        $term = trim($this->search);
        $this->recentSearches = collect([$term, ...$this->recentSearches])
            ->unique(fn ($value) => mb_strtolower($value))->take(5)->values()->all();

        return redirect()->route('lotteries.yushan', ['wish' => $id]);
    }

    public function render()
    {
        return view('livewire.trip-inline-search');
    }
}
