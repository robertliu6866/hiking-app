@props([
    'user',
    'size' => 'h-7 w-7',
])

@php
    $joinedTripsCount = $user->joinedTrips()->count();
    $hostedTripsCount = $user->trips()->count();
    $followersCount = $user->followers()->count();
    $isSelf = auth()->check() && auth()->id() === $user->id;
    $isFollowing = auth()->check() && ! $isSelf && auth()->user()->following()->whereKey($user->id)->exists();
    $level = match (true) {
        $joinedTripsCount + $hostedTripsCount >= 20 => '嚮導',
        $joinedTripsCount + $hostedTripsCount >= 10 => '熟練山友',
        $joinedTripsCount + $hostedTripsCount >= 3 => '進階山友',
        default => '新手山友',
    };
@endphp

<div x-data="{ open: false }" class="inline-flex">
    <button
        type="button"
        @click="open = true"
        class="rounded-full focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2"
        title="{{ $user->name }}"
    >
        @if ($user->avatar && ! in_array(strtolower(pathinfo($user->avatar, PATHINFO_EXTENSION)), ['heic', 'heif'], true))
            <img
                src="{{ $user->avatar_url }}?v={{ $user->updated_at?->timestamp }}"
                alt="{{ $user->name }}"
                class="{{ $size }} rounded-full border-2 border-white object-cover"
            >
        @else
            <span class="{{ $size }} flex items-center justify-center rounded-full border-2 border-white bg-slate-100 text-xs font-semibold text-slate-700">
                {{ mb_substr($user->name, 0, 1) }}
            </span>
        @endif
    </button>

    <div
        x-cloak
        x-show="open"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-end justify-center bg-black/40 px-4 py-4"
        @keydown.escape.window="open = false"
    >
        <div class="absolute inset-0" @click="open = false"></div>

        <div
            x-show="open"
            x-transition
            class="relative w-full max-w-sm rounded-3xl border border-slate-200 bg-white p-5 shadow-xl"
        >
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                    @if ($user->avatar && ! in_array(strtolower(pathinfo($user->avatar, PATHINFO_EXTENSION)), ['heic', 'heif'], true))
                        <img
                            src="{{ $user->avatar_url }}?v={{ $user->updated_at?->timestamp }}"
                            alt="{{ $user->name }}"
                            class="h-14 w-14 rounded-full border border-slate-200 object-cover"
                        >
                    @else
                        <div class="flex h-14 w-14 items-center justify-center rounded-full border border-slate-200 bg-slate-100 text-lg font-semibold text-slate-700">
                            {{ mb_substr($user->name, 0, 1) }}
                        </div>
                    @endif

                    <div>
                        <h3 class="text-base font-semibold text-slate-950">{{ $user->name }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ $level }}</p>
                    </div>
                </div>

                <button
                    type="button"
                    @click="open = false"
                    class="rounded-full border border-slate-200 px-3 py-1.5 text-sm text-slate-500"
                >
                    關閉
                </button>
            </div>

            <div class="mt-5 grid grid-cols-2 gap-2 text-sm">
                <div class="rounded-2xl border border-slate-100 px-3 py-2">
                    <div class="text-xs text-slate-400">性別</div>
                    <div class="mt-1 text-slate-900">{{ $user->gender ?: '未填寫' }}</div>
                </div>

                <div class="rounded-2xl border border-slate-100 px-3 py-2">
                    <div class="text-xs text-slate-400">登山經驗</div>
                    <div class="mt-1 text-slate-900">{{ $user->hiking_experience ?: '未填寫' }}</div>
                </div>

                <div class="rounded-2xl border border-slate-100 px-3 py-2">
                    <div class="text-xs text-slate-400">參加揪團</div>
                    <div class="mt-1 text-slate-900">{{ $joinedTripsCount }} 次</div>
                </div>

                <div class="rounded-2xl border border-slate-100 px-3 py-2">
                    <div class="text-xs text-slate-400">主辦行程</div>
                    <div class="mt-1 text-slate-900">{{ $hostedTripsCount }} 次</div>
                </div>

                <div class="rounded-2xl border border-slate-100 px-3 py-2">
                    <div class="text-xs text-slate-400">關注者</div>
                    <div class="mt-1 text-slate-900">{{ $followersCount }} 人</div>
                </div>
            </div>

            @if (! $isSelf)
                <div class="mt-4">
                    @if ($isFollowing)
                        <form method="POST" action="{{ route('users.unfollow', $user) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="ui-btn-ghost w-full">
                                已關注，取消關注
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('users.follow', $user) }}">
                            @csrf
                            <button type="submit" class="ui-btn-primary w-full">
                                關注山友
                            </button>
                        </form>
                    @endif
                </div>
            @endif

            <div class="mt-4 rounded-2xl border border-slate-100 px-3 py-3">
                <div class="flex items-center justify-between">
                    <div class="text-sm font-medium text-slate-900">山友評價</div>
                    <div class="text-sm text-slate-500">尚無評價</div>
                </div>
                <p class="mt-2 text-sm leading-6 text-slate-500">
                    評價系統尚未開放。之後可顯示守時、溝通、路線準備與安全意識。
                </p>
            </div>

            @if ($user->bio)
                <div class="mt-4 border-l-2 border-emerald-200 pl-3 text-sm leading-6 text-slate-600">
                    {{ $user->bio }}
                </div>
            @endif
        </div>
    </div>
</div>
