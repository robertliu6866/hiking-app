<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-slate-900">報名名單管理</h2>
            <a href="{{ route('trips.index') }}" class="text-sm font-medium text-emerald-700">活動列表</a>
        </div>
    </x-slot>

    <div class="page-shell">
        <div class="page-container space-y-4">
            <section class="ui-card">
                <p class="text-sm font-semibold text-emerald-700">管理員</p>
                <h1 class="mt-1 text-2xl font-semibold leading-tight text-slate-950">活動報名名單</h1>
                <p class="mt-2 text-sm leading-6 text-slate-500">
                    查看每個活動目前已報名的會員。
                </p>
            </section>

            <section class="space-y-3">
                @forelse ($trips as $trip)
                    <article class="ui-card">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-xs font-semibold text-emerald-700">
                                    {{ $trip->departure_time ? $trip->departure_time->format('Y/m/d H:i') : '時間待補' }}
                                </div>
                                <h3 class="mt-1 text-lg font-semibold leading-tight text-slate-950">{{ $trip->title }}</h3>
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ $trip->mountain ?: '山名待補' }} · {{ $trip->location ?: '地點待補' }}
                                </p>
                            </div>

                            <span class="shrink-0 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                {{ $trip->participants_count }} 人
                            </span>
                        </div>

                        <div class="mt-4 border-t border-slate-100 pt-3">
                            @forelse ($trip->participants as $participant)
                                <div class="flex items-center justify-between py-2 text-sm">
                                    <div>
                                        <div class="font-medium text-slate-900">{{ $participant->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $participant->email }}</div>
                                    </div>
                                    <div class="text-right text-xs text-slate-500">
                                        {{ $participant->phone ?: '未填手機' }}
                                    </div>
                                </div>
                            @empty
                                <div class="py-3 text-sm text-slate-500">目前沒有人報名。</div>
                            @endforelse
                        </div>
                    </article>
                @empty
                    <div class="ui-empty">
                        <div class="text-sm font-medium text-slate-900">目前沒有活動</div>
                    </div>
                @endforelse
            </section>
        </div>
    </div>
</x-app-layout>
