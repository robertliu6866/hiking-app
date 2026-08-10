@php
    $nextTrip = $joinedTrips->first();
    $otherTrips = $joinedTrips->skip(1);
@endphp

<div class="space-y-5" wire:poll.visible.30s>
    <section class="space-y-4">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="ui-muted">你好，{{ $user->name }}</p>
                <h2 class="mt-1 text-2xl font-semibold leading-tight text-slate-950">我的行程</h2>
            </div>

            <span class="shrink-0 rounded-full border px-3 py-1 text-xs font-medium {{ $statusClasses }}">
                {{ $statusLabel }}
            </span>
        </div>

        <div class="grid grid-cols-2 gap-2">
            <div class="rounded-2xl border border-slate-100 bg-white px-4 py-3">
                <div class="text-lg font-semibold text-slate-950">{{ $joinedTrips->count() }}</div>
                <div class="mt-1 text-xs text-slate-500">已報名行程</div>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white px-4 py-3">
                <div class="text-lg font-semibold text-slate-950">
                    {{ $nextTrip?->departure_time ? $nextTrip->departure_time->format('m/d') : '--' }}
                </div>
                <div class="mt-1 text-xs text-slate-500">下一趟出發</div>
            </div>
        </div>
    </section>

    <section class="space-y-3">
        <div class="flex items-center justify-between gap-3">
            <h3 class="ui-section-title">下一趟行程</h3>

            @if ($joinedTrips->count() > 0)
                <span class="text-xs font-medium text-slate-500">已報名 {{ $joinedTrips->count() }} 筆</span>
            @endif
        </div>

        @if ($nextTrip)
            <article class="ui-card">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h4 class="truncate text-base font-semibold text-slate-950">
                            {{ $nextTrip->title }}
                        </h4>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ $nextTrip->location ?? '地點待補' }}
                            @if ($nextTrip->mountain)
                                · {{ $nextTrip->mountain }}
                            @endif
                        </p>
                    </div>

                    <span class="ui-chip-hope shrink-0">
                        {{ $nextTrip->participants_count }} / {{ $nextTrip->quota }}
                    </span>
                </div>

                <dl class="mt-4 space-y-2 text-sm">
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-slate-400">出發</dt>
                        <dd class="text-right font-medium text-slate-800">
                            {{ $nextTrip->departure_time ? $nextTrip->departure_time->format('m/d H:i') : '時間待補' }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-slate-400">集合</dt>
                        <dd class="text-right font-medium text-slate-800">
                            {{ $nextTrip->meeting_point ?: '待補' }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-slate-400">主辦</dt>
                        <dd class="text-right font-medium text-slate-800">
                            {{ $nextTrip->user?->name ?? '山友' }}
                        </dd>
                    </div>
                </dl>

                <a
                    href="{{ route('trips.show', $nextTrip) }}"
                    class="ui-btn-primary mt-4 w-full"
                >
                    查看行程
                </a>
            </article>
        @else
            <div class="ui-empty">
                <div class="text-sm font-semibold text-slate-950">還沒有報名行程</div>
                <p class="mt-2 text-sm text-slate-500">先找一條適合體力與時間的路線，報名後會顯示在這裡。</p>
                <a
                    href="{{ route('trips.index') }}"
                    class="ui-btn-primary mt-4"
                >
                    找活動
                </a>
            </div>
        @endif
    </section>

    <section class="space-y-3">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h3 class="ui-section-title">我的許願狀態</h3>
                <p class="ui-muted mt-1">你已 +1 的許願，最新狀態會集中在這裡。</p>
            </div>

            <a href="{{ route('trips.index') }}" class="shrink-0 text-xs font-medium text-slate-500">
                查看許願
            </a>
        </div>

        @forelse ($joinedWishes as $wish)
            @php
                $wishSignal = match (true) {
                    $wish->users_count >= 5 => '熱門願望',
                    $wish->users_count >= 2 => '正在聚人',
                    default => '已響應',
                };
            @endphp

            <article class="ui-card">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex flex-wrap gap-2">
                            <span class="ui-chip-hope">{{ $wishSignal }}</span>
                            <span class="ui-chip">
                                {{ $wish->wished_date ? $wish->wished_date->format('Y/m/d') : '日期彈性' }}
                            </span>
                        </div>

                        <h4 class="mt-3 truncate text-base font-semibold text-slate-950">
                            {{ $wish->mountain }}
                        </h4>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ $wish->user?->name ?? '山友' }} 發起 · {{ $wish->users_count }} 人響應
                        </p>
                    </div>

                    <span class="shrink-0 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                        已 +1
                    </span>
                </div>

                @if ($wish->note)
                    <p class="mt-3 line-clamp-2 text-sm leading-6 text-slate-600">{{ $wish->note }}</p>
                @endif

                <div class="mt-4 flex items-center gap-2">
                    <a
                        href="{{ route('trips.create', [
                            'mountain' => $wish->mountain,
                            'wished_date' => optional($wish->wished_date)->toDateString(),
                            'note' => $wish->note,
                        ]) }}"
                        class="ui-btn-ghost flex-1"
                    >
                        開團
                    </a>
                </div>
            </article>
        @empty
            <div class="ui-empty">
                <div class="text-sm font-semibold text-slate-950">還沒有參加許願</div>
                <p class="mt-2 text-sm text-slate-500">看到想去的山可以先 +1，後續狀態會顯示在這裡。</p>
                <a
                    href="{{ route('trips.index') }}"
                    class="ui-btn-soft mt-4"
                >
                    查看許願
                </a>
            </div>
        @endforelse
    </section>

    @if ($otherTrips->isNotEmpty())
        <section class="space-y-3">
            <div class="flex items-center justify-between gap-3">
                <h3 class="ui-section-title">其他已報名</h3>
                <a href="{{ route('trips.index') }}" class="text-xs font-medium text-slate-500">
                    查看全部
                </a>
            </div>

            <div class="space-y-2">
                @foreach ($otherTrips->take(3) as $trip)
                    <a href="{{ route('trips.show', $trip) }}" class="block rounded-2xl border border-slate-100 bg-white px-4 py-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="truncate text-sm font-semibold text-slate-950">{{ $trip->title }}</div>
                                <div class="mt-1 text-xs text-slate-500">
                                    {{ $trip->departure_time ? $trip->departure_time->format('m/d H:i') : '時間待補' }}
                                    @if ($trip->meeting_point)
                                        · {{ $trip->meeting_point }}
                                    @endif
                                </div>
                            </div>

                            <span class="ui-chip shrink-0">{{ $trip->participants_count }} / {{ $trip->quota }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section class="grid grid-cols-2 gap-2">
        <a
            href="{{ route('trips.index') }}"
            class="ui-btn-soft"
        >
            找活動
        </a>

        <a
            href="{{ route('trips.create') }}"
            class="ui-btn-soft"
        >
            建立活動
        </a>
    </section>

    <section class="space-y-3">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h3 class="ui-section-title">你可能也會喜歡</h3>
                <p class="ui-muted mt-1">依你的地區、時間、交通與難度偏好排序。</p>
            </div>

            <a href="{{ route('profile.edit') }}" class="shrink-0 text-xs font-medium text-slate-500">
                調整偏好
            </a>
        </div>

        @if ($user->preferenceCompletionPercent() < 50)
            <div class="rounded-3xl border border-amber-100 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-800">
                補完登山偏好後，系統會更容易挑出適合你的行程。
            </div>
        @endif

        @forelse ($recommendedTrips as $trip)
            @php
                $matchReasons = $user->tripMatchReasons($trip);
            @endphp

            <article class="ui-card">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex flex-wrap gap-2">
                            <span class="ui-chip-hope">適合你</span>
                            @if ($trip->price > 0)
                                <span class="ui-chip">NT$ {{ number_format($trip->price) }}</span>
                            @else
                                <span class="ui-chip">免費</span>
                            @endif
                        </div>

                        <h4 class="mt-3 truncate text-base font-semibold text-slate-950">
                            {{ $trip->title }}
                        </h4>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ $trip->location ?? '地點待補' }}
                            @if ($trip->mountain)
                                · {{ $trip->mountain }}
                            @endif
                        </p>
                    </div>

                    <span class="ui-chip shrink-0">
                        {{ $trip->participants_count }} / {{ $trip->quota }}
                    </span>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    @forelse ($matchReasons as $reason)
                        <span class="rounded-full bg-slate-50 px-3 py-1 text-xs font-medium text-slate-600">{{ $reason }}</span>
                    @empty
                        <span class="rounded-full bg-slate-50 px-3 py-1 text-xs font-medium text-slate-600">補完偏好後會更準</span>
                    @endforelse
                </div>

                <a href="{{ route('trips.show', $trip) }}" class="ui-btn-ghost mt-4 w-full">
                    查看行程
                </a>
            </article>
        @empty
            <div class="ui-empty">
                <div class="text-sm font-semibold text-slate-950">目前沒有可推薦行程</div>
                <p class="mt-2 text-sm text-slate-500">可以先許願或建立新的行程。</p>
            </div>
        @endforelse
    </section>
</div>
