<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-slate-900">我的報名</h2>
            <a href="{{ route('trips.index') }}" class="text-sm font-medium text-emerald-700">看活動列表</a>
        </div>
    </x-slot>

    <div class="page-shell">
        <div class="page-container space-y-4">
            <section class="ui-card">
                <p class="text-sm font-semibold text-emerald-700">會員中心</p>
                <h1 class="mt-1 text-2xl font-semibold leading-tight text-slate-950">我的報名</h1>
                <p class="mt-2 text-sm leading-6 text-slate-500">
                    這裡會列出你已報名的活動，以及已參加的許願行程。
                </p>
            </section>

            <section class="space-y-3">
                @forelse ($joinedTrips as $trip)
                    <article class="ui-card">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-xs font-semibold text-emerald-700">
                                    {{ $trip->departure_time ? $trip->departure_time->format('Y/m/d H:i') : '時間待補' }}
                                </div>
                                <h3 class="mt-1 text-lg font-semibold leading-tight text-slate-950">{{ $trip->title }}</h3>
                                <p class="mt-1 text-sm leading-6 text-slate-500">
                                    {{ $trip->mountain ?: '山名待補' }} · {{ $trip->meeting_point ?: '集合地點待補' }}
                                </p>
                            </div>

                            <span class="shrink-0 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                已報名
                            </span>
                        </div>

                        <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
                            <span class="text-xs text-slate-500">主辦：{{ $trip->user?->name ?? '管理員' }}</span>
                            <a href="{{ route('trips.show', $trip) }}" class="text-xs font-semibold text-emerald-700">查看詳情</a>
                        </div>
                    </article>
                @empty
                    <div class="ui-empty">
                        <div class="text-sm font-medium text-slate-900">尚未報名活動</div>
                        <p class="mt-1 text-sm text-slate-500">先到活動列表選一個活動報名。</p>
                        <a href="{{ route('trips.index') }}" class="ui-btn-primary mt-4">
                            看活動列表
                        </a>
                    </div>
                @endforelse
            </section>

            <section class="space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">我的許願</h2>
                        <p class="mt-1 text-sm text-slate-500">你已 +1 的許願行程也會顯示在這裡。</p>
                    </div>
                </div>

                @forelse ($joinedWishes as $wish)
                    <article class="ui-card">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-xs font-semibold text-emerald-700">
                                    {{ $wish->wished_date ? $wish->wished_date->format('Y/m/d') : '日期彈性' }}
                                </div>
                                <h3 class="mt-1 text-lg font-semibold leading-tight text-slate-950">{{ $wish->mountain }}</h3>
                                <p class="mt-1 text-sm leading-6 text-slate-500">
                                    {{ $wish->user?->name ?? '山友' }} 發起
                                    @if ($wish->homepage_group === 'guided')
                                        · 有人可帶
                                    @elseif ($wish->homepage_group === 'self')
                                        · 自由成團
                                    @endif
                                </p>
                            </div>

                            <span class="shrink-0 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                已 +1
                            </span>
                        </div>

                        @if ($wish->note)
                            <p class="mt-3 text-sm leading-6 text-slate-600">{{ $wish->note }}</p>
                        @endif

                        <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
                            <div class="min-w-0">
                                <div class="text-xs text-slate-500">一起許願的山友</div>
                                <div class="mt-2 flex items-center">
                                    @foreach ($wish->users->take(5) as $participant)
                                        <div class="-ml-1 first:ml-0">
                                            <x-member-profile-popover :user="$participant" />
                                        </div>
                                    @endforeach

                                    @if ($wish->users_count > 5)
                                        <span class="ml-2 text-xs text-slate-500">+{{ $wish->users_count - 5 }}</span>
                                    @endif
                                </div>
                            </div>

                            <span class="text-xs text-slate-500">{{ $wish->users_count }} 人響應</span>
                        </div>
                    </article>
                @empty
                    <div class="ui-empty">
                        <div class="text-sm font-medium text-slate-900">尚未參加許願行程</div>
                        <p class="mt-1 text-sm text-slate-500">看到想去的山先 +1，後續同行山友會顯示在這裡。</p>
                    </div>
                @endforelse
            </section>
        </div>
    </div>
</x-app-layout>
