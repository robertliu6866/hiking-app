<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-slate-900">活動詳情</h2>
            <a href="{{ route('trips.index') }}" class="text-sm font-medium text-slate-500">返回列表</a>
        </div>
    </x-slot>

    @php
        $participantsCount = $trip->participants_count ?? $trip->participants->count();
        $pendingOrdersCount = $trip->pending_orders_count ?? 0;
        $remainingSeats = max(0, $trip->quota - $participantsCount - $pendingOrdersCount);
        $hasJoinedTrip = $trip->participants->contains(auth()->id());
        $statusLabels = [
            'open' => '報名中',
            'full' => '已滿',
            'closed' => '已截止',
            'completed' => '已完成',
        ];
    @endphp

    <div class="page-shell">
        <div class="page-container space-y-4">
            @if (session('status') === 'trip-joined')
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">已完成報名。</div>
            @elseif (session('status') === 'trip-canceled')
                <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">已取消報名。</div>
            @endif

            @error('trip')
                <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</div>
            @enderror

            <section class="ui-card">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-emerald-700">{{ $statusLabels[$trip->status] ?? $trip->status }}</p>
                        <h1 class="mt-1 text-2xl font-semibold leading-tight text-slate-950">{{ $trip->title }}</h1>
                        <p class="mt-2 text-sm leading-6 text-slate-500">
                            {{ $trip->mountain ?: '山名待補' }} · {{ $trip->location ?: '地點待補' }}
                        </p>
                    </div>

                    <div class="shrink-0 rounded-2xl bg-emerald-50 px-3 py-2 text-center">
                        <div class="text-lg font-semibold text-emerald-700">{{ $remainingSeats }}</div>
                        <div class="text-[11px] text-emerald-700">剩餘名額</div>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    <span class="ui-chip">{{ $trip->departure_time ? $trip->departure_time->format('m/d H:i 出發') : '時間待補' }}</span>
                    <span class="ui-chip">{{ $trip->meeting_point ?: '集合地待補' }}</span>
                    <span class="ui-chip">{{ $trip->price > 0 ? 'NT$ '.number_format($trip->price) : '免費活動' }}</span>
                    <span class="ui-chip">{{ $remainingSeats === 0 ? '額滿' : '還有 '.$remainingSeats.' 位' }}</span>
                </div>

                <div class="mt-4 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3">
                    <div class="text-sm font-semibold text-emerald-950">
                        {{ $hasJoinedTrip ? '你已經報名這個活動' : '報名前先確認這團是否適合你' }}
                    </div>
                    <p class="mt-1 text-sm leading-6 text-emerald-800">
                        {{ $hasJoinedTrip ? '接下來請留意付款、集合與主辦通知；若要退出，請使用下方次要操作。' : '重點先看集合方式、費用、主辦者與目前名額，確認沒問題再往下報名。' }}
                    </p>
                </div>

                <div class="mt-5 divide-y divide-slate-100 text-sm">
                    <div class="flex items-start justify-between gap-4 py-3 first:pt-0">
                        <div class="text-slate-400">出發時間</div>
                        <div class="text-right font-medium text-slate-900">{{ $trip->departure_time ? $trip->departure_time->format('Y/m/d H:i') : '待補' }}</div>
                    </div>
                    <div class="flex items-start justify-between gap-4 py-3">
                        <div class="text-slate-400">集合地點</div>
                        <div class="text-right font-medium text-slate-900">{{ $trip->meeting_point ?: '待補' }}</div>
                    </div>
                    <div class="flex items-start justify-between gap-4 py-3">
                        <div class="text-slate-400">費用</div>
                        <div class="text-right font-medium text-slate-900">{{ $trip->price > 0 ? 'NT$ '.number_format($trip->price) : '免費' }}</div>
                    </div>
                    <div class="flex items-start justify-between gap-4 py-3">
                        <div class="text-slate-400">名額</div>
                        <div class="text-right font-medium text-slate-900">{{ $participantsCount }} / {{ $trip->quota }} 已報名</div>
                    </div>
                    <div class="flex items-start justify-between gap-4 py-3 last:pb-0">
                        <div class="text-slate-400">主辦</div>
                        <div class="text-right font-medium text-slate-900">{{ $trip->user?->name ?? '管理員' }}</div>
                    </div>
                </div>
            </section>

            @if ($trip->description)
                <section class="ui-card">
                    <h3 class="ui-section-title">活動說明</h3>
                    <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $trip->description }}</p>
                </section>
            @endif

            <livewire:trip-join-control :tripId="$trip->id" variant="detail" />
        </div>
    </div>
</x-app-layout>
