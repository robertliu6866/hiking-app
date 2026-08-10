<article
    id="weekend-{{ $dateKey }}"
    wire:key="homepage-weekend-panel-{{ $selectedPeak }}-{{ $dateKey }}"
    class="w-full shrink-0 rounded-[1.5rem] border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/60"
>
    @php
        $redirectTo = url('/').'?peak='.urlencode($selectedPeak).'&date='.$dateKey;
    @endphp

    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="text-sm font-semibold text-emerald-700">週{{ $weekdayLabel }}</p>
            <h2 class="mt-1 text-xl font-semibold text-slate-950">{{ $displayDate }} {{ $selectedPeak }}</h2>
        </div>
        <div class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
            {{ $dayWishes->sum('active_people_count') }} 人許願
        </div>
    </div>

    <div class="mt-4 space-y-4">
        @foreach ([['key' => 'guided', 'label' => '想跟團', 'items' => $guidedWishes, 'empty' => '目前還沒有可跟的團，先 +1 讓主揪知道有人想去。'], ['key' => 'self', 'label' => '想揪人', 'items' => $selfOrganizedWishes, 'empty' => '先揪人，等人數夠再開團。']] as $group)
            <div wire:key="homepage-weekend-group-{{ $dateKey }}-{{ $group['label'] }}-{{ $refreshKey }}">
                <div class="mb-2 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-semibold text-slate-900">{{ $group['label'] }}</h3>
                        <span
                            @class([
                                'rounded-full px-2 py-0.5 text-[11px] font-semibold',
                                'bg-emerald-50 text-emerald-700' => $group['key'] === 'guided',
                                'bg-sky-50 text-sky-700' => $group['key'] === 'self',
                            ])
                        >
                            {{ $group['key'] === 'guided' ? '可成團' : '揪人中' }}
                        </span>
                    </div>
                    <span class="text-xs font-medium text-slate-400">{{ $group['items']->count() }} 筆</span>
                </div>

                @if ($group['items']->isEmpty())
                    <div
                        wire:key="homepage-weekend-empty-{{ $dateKey }}-{{ $group['label'] }}-{{ $refreshKey }}"
                        class="rounded-2xl bg-slate-50 p-4"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        @class([
                                            'rounded-full px-2 py-0.5 text-[11px] font-semibold',
                                            'bg-emerald-100 text-emerald-700' => $group['key'] === 'guided',
                                            'bg-sky-100 text-sky-700' => $group['key'] === 'self',
                                        ])
                                    >
                                        {{ $group['key'] === 'guided' ? '等開團' : '先揪人' }}
                                    </span>
                                    <span class="text-xs text-slate-500">{{ $group['label'] }} · 0 人 +1</span>
                                </div>
                                <p class="mt-3 text-sm leading-6 text-slate-500">{{ $group['empty'] }}</p>
                            </div>

                            <div class="w-28 shrink-0">
                                @auth
                                    <form
                                        method="POST"
                                        action="{{ route('trip-wishes.store') }}"
                                        x-data="{ busy: false, label: '+1' }"
                                        x-on:submit.prevent="
                                            if (busy) return;
                                            busy = true;
                                            label = '處理中';
                                            Promise.resolve($wire.createWish(@js($group['key'])))
                                                .catch(() => $el.submit())
                                                .finally(() => {
                                                    busy = false;
                                                    label = '+1';
                                                });
                                        "
                                    >
                                        @csrf
                                        <input type="hidden" name="mountain" value="{{ $selectedPeak }}">
                                        <input type="hidden" name="wished_date" value="{{ $dateKey }}">
                                        <input type="hidden" name="note" value="首頁週末許願">
                                        <input type="hidden" name="homepage_group" value="{{ $group['key'] }}">
                                        <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
                                        <button
                                            type="submit"
                                            x-bind:disabled="busy"
                                            class="flex h-9 w-full items-center justify-center rounded-full bg-emerald-600 px-4 py-2 text-center text-sm font-semibold text-white disabled:cursor-wait disabled:opacity-70"
                                        >
                                            <span x-text="label">+1</span>
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('register', ['redirect_to' => $redirectTo]) }}" class="flex h-9 w-full items-center justify-center rounded-full bg-emerald-600 px-4 py-2 text-center text-sm font-semibold text-white">
                                        加入 +1
                                    </a>
                                @endauth

                                <div class="mt-3 flex h-7 min-h-7 items-center overflow-hidden">
                                    <span class="block h-7 w-7 shrink-0 rounded-full opacity-0"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="space-y-2">
                        @foreach ($group['items'] as $wish)
                            @php
                                $hasJoinedWish = auth()->check() && $wish->users->contains(auth()->id());
                            @endphp

                            @guest
                                @php
                                    $joinedUsers = $wish->participantsForHomepage;
                                @endphp
                            @endguest

                            <div
                                wire:key="homepage-weekend-wish-{{ $wish->id }}-{{ $hasJoinedWish ? 'joined' : 'open' }}-{{ $wish->users_count }}-{{ $refreshKey }}"
                                class="rounded-2xl bg-slate-50 p-4"
                                x-data="{
                                    hasJoined: @js($hasJoinedWish),
                                    count: @js($wish->active_people_count),
                                    busy: false,
                                    toggle() {
                                        if (this.busy) return;

                                        this.hasJoined = ! this.hasJoined;
                                        this.count = Math.max(0, this.count + (this.hasJoined ? 1 : -1));
                                        this.busy = true;

                                        Promise.resolve($wire.toggleWish({{ $wish->id }}))
                                            .catch(() => this.$refs.fallback.submit())
                                            .finally(() => this.busy = false);
                                    },
                                    buttonLabel() {
                                        return this.hasJoined ? '取消報名' : '+1';
                                    }
                                }"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span
                                                @class([
                                                    'rounded-full px-2 py-0.5 text-[11px] font-semibold',
                                                    'bg-emerald-100 text-emerald-700' => $wish->has_homepage_guide,
                                                    'bg-sky-100 text-sky-700' => ! $wish->has_homepage_guide,
                                                ])
                                            >
                                                {{ $wish->has_homepage_guide ? '可成團' : '揪人中' }}
                                            </span>
                                            <span class="text-xs text-slate-500">
                                                {{ $wish->user?->name ?? '山友' }} 發起 · <span x-text="count">{{ $wish->active_people_count }}</span> 人 +1
                                            </span>
                                        </div>
                                        @if ($wish->note)
                                            <p class="mt-3 text-sm leading-6 text-slate-600">{{ $wish->note }}</p>
                                        @endif
                                    </div>

                                    <div class="w-28 shrink-0">
                                        @auth
                                            <form
                                                x-ref="fallback"
                                                method="POST"
                                                action="{{ route('trip-wishes.join', $wish) }}"
                                                x-on:submit.prevent="toggle()"
                                            >
                                                @csrf
                                                <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
                                                <button
                                                    type="submit"
                                                    x-bind:disabled="busy"
                                                    class="flex h-9 w-full items-center justify-center rounded-full bg-emerald-600 px-4 py-2 text-sm font-semibold text-white disabled:cursor-wait disabled:opacity-70"
                                                >
                                                    <span x-show="! busy" x-text="buttonLabel()">{{ $hasJoinedWish ? '取消報名' : '+1' }}</span>
                                                    <span x-cloak x-show="busy">處理中</span>
                                                </button>
                                            </form>

                                            <div class="mt-3 flex h-7 min-h-7 items-center overflow-hidden">
                                                @forelse ($wish->participantsForHomepage as $joinedUser)
                                                    <div
                                                        class="h-7 w-7 shrink-0 overflow-hidden rounded-full border-2 border-white bg-slate-100 shadow-sm -ml-1 first:ml-0"
                                                        title="{{ $joinedUser->name }}"
                                                    >
                                                        @if ($joinedUser->avatar && ! in_array(strtolower(pathinfo($joinedUser->avatar, PATHINFO_EXTENSION)), ['heic', 'heif'], true))
                                                            <img
                                                                src="{{ $joinedUser->avatar_url }}?v={{ $joinedUser->updated_at?->timestamp }}"
                                                                alt="{{ $joinedUser->name }}"
                                                                class="h-full w-full object-cover"
                                                            >
                                                        @else
                                                            <span class="flex h-full w-full items-center justify-center text-xs font-semibold text-slate-700">
                                                                {{ mb_substr($joinedUser->name, 0, 1) }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                @empty
                                                    <span class="block h-7 w-7 shrink-0 rounded-full opacity-0"></span>
                                                @endforelse
                                            </div>
                                        @else
                                            <a href="{{ route('register', ['redirect_to' => $redirectTo]) }}" class="flex h-9 w-full items-center justify-center rounded-full bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">
                                                加入 +1
                                            </a>

                                            <div class="mt-3 flex h-7 min-h-7 items-center overflow-hidden">
                                                @forelse ($joinedUsers as $joinedUser)
                                                    <div
                                                        class="h-7 w-7 shrink-0 overflow-hidden rounded-full border-2 border-white bg-slate-100 shadow-sm -ml-1 first:ml-0"
                                                        title="{{ $joinedUser->name }}"
                                                    >
                                                        @if ($joinedUser->avatar && ! in_array(strtolower(pathinfo($joinedUser->avatar, PATHINFO_EXTENSION)), ['heic', 'heif'], true))
                                                            <img
                                                                src="{{ $joinedUser->avatar_url }}?v={{ $joinedUser->updated_at?->timestamp }}"
                                                                alt="{{ $joinedUser->name }}"
                                                                class="h-full w-full object-cover"
                                                            >
                                                        @else
                                                            <span class="flex h-full w-full items-center justify-center text-xs font-semibold text-slate-700">
                                                                {{ mb_substr($joinedUser->name, 0, 1) }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                @empty
                                                    <span class="block h-7 w-7 shrink-0 rounded-full opacity-0"></span>
                                                @endforelse
                                            </div>
                                        @endauth
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</article>
