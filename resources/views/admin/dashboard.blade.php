<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-slate-900">管理後台</h2>
            <a href="{{ route('trips.index') }}" class="text-sm font-medium text-emerald-700">查看活動</a>
        </div>
    </x-slot>

    <div class="page-shell">
        <div class="page-container space-y-4">
            <section class="ui-card">
                <p class="text-sm font-semibold text-emerald-700">管理員</p>
                <h1 class="mt-1 text-2xl font-semibold leading-tight text-slate-950">今天想先看什麼？</h1>
                <p class="mt-2 text-sm leading-6 text-slate-500">會員、活動與報名狀況都在這裡。</p>
            </section>

            <section class="grid grid-cols-2 gap-3">
                <div class="ui-panel">
                    <p class="text-xs font-medium text-slate-500">會員數</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-950">{{ $memberCount }}</p>
                </div>
                <div class="ui-panel">
                    <p class="text-xs font-medium text-slate-500">即將出發</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-950">{{ $upcomingTripCount }}</p>
                </div>
                <div class="ui-panel">
                    <p class="text-xs font-medium text-slate-500">已報名人次</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-950">{{ $registrationCount }}</p>
                </div>
                <div class="ui-panel">
                    <p class="text-xs font-medium text-slate-500">待確認付款</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-950">{{ $pendingPaymentCount }}</p>
                </div>
            </section>

            <a href="{{ route('admin.trips.index') }}" class="ui-btn-primary w-full">管理活動與報名名單</a>

            <section class="ui-card">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="ui-section-title">近期活動</h2>
                    <a href="{{ route('admin.trips.index') }}" class="text-sm font-medium text-emerald-700">全部活動</a>
                </div>

                <div class="mt-3 divide-y divide-slate-100">
                    @forelse ($upcomingTrips as $trip)
                        <div class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-900">{{ $trip->title }}</p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $trip->departure_time?->format('m/d H:i') ?? '時間待補' }} · {{ $trip->mountain ?: '山名待補' }}
                                </p>
                            </div>
                            <span class="shrink-0 text-sm font-semibold text-emerald-700">{{ $trip->participants_count }} 人</span>
                        </div>
                    @empty
                        <p class="py-2 text-sm text-slate-500">還沒有即將出發的活動。</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
