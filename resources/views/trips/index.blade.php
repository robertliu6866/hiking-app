<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-slate-900">活動列表</h2>
            <div class="flex items-center gap-3">
                @if (auth()->user()->is_admin)
                    <a href="{{ route('trips.create') }}" class="inline-flex items-center gap-1.5 rounded-full bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        發起活動
                    </a>
                @endif
                <a href="{{ route('dashboard') }}" class="text-sm font-medium text-emerald-700">我的報名</a>
            </div>
        </div>
    </x-slot>

    <div class="page-shell">
        <div class="page-container space-y-4">
            @if (session('status') === 'member-created')
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    註冊完成，現在可以報名活動。
                </div>
            @endif

            @if (session('status') === 'trip-joined')
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    已完成報名。
                </div>
            @elseif (session('status') === 'trip-canceled')
                <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                    已取消報名。
                </div>

            @endif

            @error('trip')
                <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</div>
            @enderror

            <section class="ui-card bg-gradient-to-br from-emerald-50 to-teal-50 border-emerald-200">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100">
                        <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-emerald-900">沒有你想去的山嗎？</h3>
                        <p class="mt-0.5 text-sm text-emerald-700">那就許願吧！</p>
                    </div>
                </div>
                <a href="{{ route('lotteries.yushan') }}" class="mt-4 block w-full rounded-2xl bg-emerald-600 py-2.5 text-center text-sm font-medium text-white hover:bg-emerald-700 transition">
                    前往許願
                </a>
            </section>

            <section class="ui-card border-slate-200 bg-white/90">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-base font-semibold text-slate-950">找一個適合你的團</h3>
                        <p class="mt-1 text-sm leading-6 text-slate-500">
                            選一個活動報名。先看日期、集合點、費用和主辦經驗，再決定要不要報名。
                        </p>
                    </div>
                    <span class="ui-chip">{{ $trips->count() }} 個活動</span>
                </div>
            </section>

            <section class="space-y-3">
                @forelse ($trips as $trip)
                    @php
                        $participantsCount = $trip->participants_count ?? $trip->participants->count();
                        $pendingOrdersCount = $trip->pending_orders_count ?? 0;
                        $remainingSeats = max(0, $trip->quota - $participantsCount - $pendingOrdersCount);
                        $hasJoinedTrip = $trip->participants->contains(auth()->id());
                        $hostTripCount = $trip->user?->trips()->count() ?? 0;
                        $hostJoinedCount = $trip->user?->joinedTrips()->count() ?? 0;
                        $hostExperienceCount = $hostTripCount + $hostJoinedCount;
                        $hostLevel = match (true) {
                            $hostExperienceCount >= 20 => '資深嚮導',
                            $hostExperienceCount >= 10 => '熟練山友',
                            $hostExperienceCount >= 3 => '進階山友',
                            default => '新手山友',
                        };
                        $urgencyLabel = match (true) {
                            $remainingSeats === 0 => '已額滿',
                            $remainingSeats <= 2 => '名額快滿',
                            $remainingSeats <= 5 => '剩少量名額',
                            default => '可報名',
                        };
                    @endphp

                    <article class="ui-card">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-xs font-semibold text-emerald-700">
                                    {{ $trip->departure_time ? $trip->departure_time->format('Y/m/d H:i') : '時間待補' }}
                                </div>
                                <h3 class="mt-1 text-lg font-semibold leading-tight text-slate-950">
                                    {{ $trip->title }}
                                </h3>
                                <p class="mt-1 text-sm leading-6 text-slate-500">
                                    {{ $trip->mountain ?: '山名待補' }} · {{ $trip->location ?: '地點待補' }}
                                </p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span class="ui-chip">{{ $urgencyLabel }}</span>
                                    <span class="ui-chip">主辦 {{ $trip->user?->name ?? '山友' }}</span>
                                    <span class="ui-chip">{{ $hostLevel }}</span>
                                </div>
                            </div>
                            <div class="shrink-0 rounded-2xl bg-emerald-50 px-3 py-2 text-center">
                                <div class="text-base font-semibold text-emerald-700">{{ $remainingSeats }}</div>
                                <div class="text-[11px] text-emerald-700">剩餘</div>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-2 text-sm">
                            <div class="rounded-2xl bg-slate-50 px-3 py-2">
                                <div class="text-xs text-slate-400">集合地點</div>
                                <div class="mt-1 font-medium text-slate-800">{{ $trip->meeting_point ?: '待補' }}</div>
                            </div>
                            <div class="rounded-2xl bg-slate-50 px-3 py-2">
                                <div class="text-xs text-slate-400">費用</div>
                                <div class="mt-1 font-medium text-slate-800">{{ $trip->price > 0 ? 'NT$ '.number_format($trip->price) : '免費' }}</div>
                            </div>
                        </div>

                        <div class="mt-2 rounded-2xl bg-slate-50 px-3 py-2 text-sm text-slate-600">
                            {{ $hasJoinedTrip ? '你已經在這團裡，可以直接回來查看後續資訊。' : '如果這團看起來適合你，下一步就去看詳情並完成報名。' }}
                        </div>

                        <div class="mt-4 flex items-center justify-between gap-3 border-t border-slate-100 pt-3">
                            <div class="text-xs text-slate-500">
                                已報名 {{ $participantsCount }} / {{ $trip->quota }}
                                @if ($pendingOrdersCount > 0)
                                    · {{ $pendingOrdersCount }} 人待付款
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('trips.show', $trip) }}" class="{{ $hasJoinedTrip ? 'ui-btn-ghost' : 'ui-btn-primary' }} min-h-10 px-4 py-2 text-xs">
                                    {{ $hasJoinedTrip ? '查看後續' : '看詳情並報名' }}
                                </a>
                                @if ($hasJoinedTrip)
                                    <span class="rounded-full bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700">已報名</span>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="ui-empty">
                        <div class="text-sm font-medium text-slate-900">目前沒有活動</div>
                        <p class="mt-1 text-sm text-slate-500">請稍後再回來查看。</p>
                    </div>
                @endforelse
            </section>
        </div>
    </div>
</x-app-layout>
