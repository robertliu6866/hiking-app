@php
    $participants = $trip->participants;
    $participantsCount = $trip->participants_count;
    $pendingOrders = $trip->pendingOrders;
    $pendingOrdersCount = $trip->pending_orders_count ?? $pendingOrders->count();
    $reservedSeatsCount = $participantsCount + $pendingOrdersCount;
    $hasJoinedTrip = $participants->contains(auth()->id());
    $currentPendingOrder = $pendingOrders->firstWhere('user_id', auth()->id());
    $isFull = $reservedSeatsCount >= $trip->quota;
    $filledPercent = $trip->quota > 0 ? min(100, round(($reservedSeatsCount / $trip->quota) * 100)) : 0;
    $currentUser = auth()->user()->loadCount(['joinedTrips', 'trips']);
    $currentUserLevel = match (true) {
        ($currentUser->joined_trips_count + $currentUser->trips_count) >= 20 => '嚮導',
        ($currentUser->joined_trips_count + $currentUser->trips_count) >= 10 => '熟練山友',
        ($currentUser->joined_trips_count + $currentUser->trips_count) >= 3 => '進階山友',
        default => '新手山友',
    };
    $otherParticipants = $participants->where('id', '!=', auth()->id());
    $registrations = $trip->registrations->keyBy('user_id');
@endphp

<div>
    <livewire:trip-registration-form :tripId="$trip->id" />

@if ($variant === 'detail')
    <div
        class="space-y-4"
        x-data="{ notice: '' }"
        x-on:trip-notice.window="notice = $event.detail.message; setTimeout(() => notice = '', 2600)"
    >
        <section class="ui-card">
            <div class="flex items-center justify-between">
                <h3 class="ui-section-title">報名狀態</h3>
                <span class="ui-chip" x-text="(count + pendingCount) + ' / ' + quota">
                    {{ $reservedSeatsCount }} / {{ $trip->quota }}
                </span>
            </div>

            <div class="mt-4 grid gap-1" style="grid-template-columns: repeat({{ max(1, $trip->quota) }}, minmax(0, 1fr));">
                @for ($seat = 1; $seat <= max(1, $trip->quota); $seat++)
                    <div
                        class="h-3 rounded-full transition-colors duration-300"
                        style="background-color: {{ $reservedSeatsCount >= $seat ? '#059669' : '#d1d5db' }};"
                    ></div>
                @endfor
            </div>

            <p class="mt-3 text-sm text-slate-600">
                @if ($hasJoinedTrip)
                    你已在名單中，接下來只要留意付款或主辦通知即可。
                @elseif ($currentPendingOrder)
                    你的名額已先保留，完成付款後就會正式加入。
                @elseif ($isFull)
                    目前名額已滿，若有人取消可再回來看看。
                @else
                    目前還有 {{ max(0, $trip->quota - $reservedSeatsCount) }} 個位置，確認資料後就能完成報名。
                @endif
            </p>

            @if ($hasJoinedTrip)
                <div class="mt-5 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    報名已完成。若行程改變，你仍可取消，但這會釋出你的名額。
                </div>
                <form method="POST" action="{{ route('trips.join', $trip) }}" wire:submit.prevent="toggle" class="mt-3" onsubmit="return confirm('確定要取消報名嗎？取消後名額會立即釋出。')">
                    @csrf
                    <button
                        type="submit"
                        class="ui-btn-ghost w-full"
                    >
                        取消報名
                    </button>
                </form>
            @elseif ($currentPendingOrder)
                <a
                    href="{{ route('payments.show', $currentPendingOrder) }}"
                    class="ui-btn-primary mt-5 block w-full text-center"
                >
                    前往完成付款
                </a>
                <p class="mt-2 text-center text-xs text-slate-500">名額保留中，請在時限內完成付款。</p>
            @else
                <form method="POST" action="{{ route('trips.join', $trip) }}" wire:submit.prevent="openRegistration" class="mt-5">
                    @csrf
                    <button
                        type="submit"
                        @disabled($isFull)
                        class="{{ $isFull ? 'ui-btn-soft' : 'ui-btn-primary' }} w-full disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        {{ $isFull ? '已滿' : '我要參加' }}
                    </button>
                </form>
                <p class="mt-2 text-center text-xs text-slate-500">
                    {{ $trip->price > 0 ? '我要報名的下一步是先填資料並保留名額，接著前往付款。' : '我要報名的下一步是先填基本資料，送出後就會直接加入名單。' }}
                </p>
            @endif

            <div x-cloak x-show="notice" x-transition class="mt-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" x-text="notice"></div>
        </section>

        <section class="ui-card">
            <div class="flex items-center justify-between">
                <h3 class="ui-section-title">已報名山友</h3>
                <span class="text-xs text-slate-500">{{ $participantsCount }} 人</span>
            </div>

            <div class="mt-4 space-y-3">
                @if($hasJoinedTrip)
                    <div class="flex items-center gap-3 rounded-2xl border border-emerald-100 bg-emerald-50 px-3 py-3">
                        <x-member-profile-popover :user="$currentUser" size="h-10 w-10" />

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <div class="truncate text-sm font-medium text-emerald-950">
                                    {{ $currentUser->name }}
                                </div>
                                <span class="shrink-0 text-xs text-emerald-600">
                                    {{ $currentUserLevel }}
                                </span>
                            </div>

                            <div class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-xs text-emerald-700">
                                <span>{{ $currentUser->gender ?: '性別未填' }}</span>
                                <span>參加 {{ $currentUser->joined_trips_count }} 次</span>
                                <span>主辦 {{ $currentUser->trips_count }} 次</span>
                            </div>
                        </div>
                    </div>
                @endif

                @forelse ($otherParticipants as $participant)
                    @php
                        $participantLevel = match (true) {
                            ($participant->joined_trips_count + $participant->trips_count) >= 20 => '嚮導',
                            ($participant->joined_trips_count + $participant->trips_count) >= 10 => '熟練山友',
                            ($participant->joined_trips_count + $participant->trips_count) >= 3 => '進階山友',
                            default => '新手山友',
                        };
                    @endphp

                    <div class="flex items-center gap-3 rounded-2xl border border-slate-100 px-3 py-3">
                        <x-member-profile-popover :user="$participant" size="h-10 w-10" />

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <div class="truncate text-sm font-medium text-slate-950">
                                    {{ $participant->name }}
                                </div>
                                <span class="shrink-0 text-xs text-slate-500">
                                    {{ $participantLevel }}
                                </span>
                            </div>

                            <div class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-xs text-slate-500">
                                <span>{{ $participant->gender ?: '性別未填' }}</span>
                                <span>參加 {{ $participant->joined_trips_count }} 次</span>
                                <span>主辦 {{ $participant->trips_count }} 次</span>
                            </div>
                        </div>
                    </div>
                @empty
                    @if($participantsCount === 0)
                        <span class="text-sm text-slate-500">尚無人報名</span>
                    @endif
                @endforelse
            </div>
        </section>

        @if ($pendingOrders->isNotEmpty())
            <section class="ui-card">
                <div class="flex items-center justify-between">
                    <h3 class="ui-section-title">準團員</h3>
                    <span class="text-xs text-slate-500">{{ $pendingOrdersCount }} 人待確認</span>
                </div>

                <div class="mt-4 space-y-3">
                    @foreach ($pendingOrders as $pendingOrder)
                        @php
                            $pendingUser = $pendingOrder->user;
                            $pendingLevel = match (true) {
                                ($pendingUser->joined_trips_count + $pendingUser->trips_count) >= 20 => '嚮導',
                                ($pendingUser->joined_trips_count + $pendingUser->trips_count) >= 10 => '熟練山友',
                                ($pendingUser->joined_trips_count + $pendingUser->trips_count) >= 3 => '進階山友',
                                default => '新手山友',
                            };
                            $pendingLabel = $pendingOrder->status === \App\Models\TripOrder::STATUS_BANK_TRANSFER_PENDING
                                ? '已送出匯款通知'
                                : '付款處理中';
                        @endphp

                        <div class="flex items-center gap-3 rounded-2xl border border-amber-100 bg-amber-50 px-3 py-3">
                            <x-member-profile-popover :user="$pendingUser" size="h-10 w-10" />

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="truncate text-sm font-medium text-amber-950">
                                        {{ $pendingUser->name }}
                                    </div>
                                    <span class="shrink-0 text-xs text-amber-700">{{ $pendingLabel }}</span>
                                </div>

                                <div class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-xs text-amber-700">
                                    <span>{{ $pendingLevel }}</span>
                                    <span>NT$ {{ number_format($pendingOrder->amount) }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@else
    <div
        class="space-y-3"
        wire:key="trip-join-{{ $trip->id }}"
    >
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <div class="text-sm text-slate-500">主辦 · {{ $trip->user?->name ?? '山友' }}</div>

                <div class="mt-2 flex min-h-7 items-center">
                    @if($hasJoinedTrip)
                        <div class="-ml-1 first:ml-0">
                            <x-member-profile-popover :user="$currentUser" />
                        </div>
                    @endif

                    @foreach ($otherParticipants->take(6) as $participant)
                        <div class="-ml-1 first:ml-0">
                            <x-member-profile-popover :user="$participant" />
                        </div>
                    @endforeach

                    @if ($participantsCount > 6)
                        <span class="ml-2 text-xs text-slate-500">
                            +{{ $participantsCount - 6 }}
                        </span>
                    @endif

                    @if($participantsCount === 0)
                        <span class="text-xs text-slate-400">
                            尚無人報名
                        </span>
                    @endif
                </div>
            </div>

            <span class="ui-chip shrink-0">
                {{ $reservedSeatsCount }} / {{ $trip->quota }}
            </span>
        </div>

        @if ($pendingOrdersCount > 0)
            <div class="rounded-2xl border border-amber-100 bg-amber-50 px-3 py-2 text-xs text-amber-700">
                {{ $pendingOrdersCount }} 位準團員待確認
            </div>
        @endif

        <div class="grid grid-cols-2 gap-2">
            <a
                href="{{ route('trips.show', $trip) }}"
                class="ui-btn-ghost h-11 min-h-11 px-3 py-2"
            >
                查看
            </a>

            @if ($hasJoinedTrip)
                <form method="POST" action="{{ route('trips.join', $trip) }}">
                    @csrf
                    <button
                        type="submit"
                        class="ui-btn-soft inline-flex h-11 min-h-11 w-full px-3 py-2"
                        onclick="return confirm('確定要取消報名嗎？')"
                    >
                        取消報名
                    </button>
                </form>
            @elseif ($currentPendingOrder)
                <a
                    href="{{ route('payments.show', $currentPendingOrder) }}"
                    class="ui-btn-soft inline-flex h-11 min-h-11 w-full items-center justify-center px-3 py-2"
                >
                    狀態
                </a>
            @else
                    <button
                        type="button"
                        @if(! $isFull)
                            wire:click="openRegistration"
                        @endif
                        @disabled($isFull)
                        class="{{ $isFull ? 'ui-btn-soft' : 'ui-btn-primary' }} inline-flex h-11 min-h-11 w-full px-3 py-2 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        {{ $isFull ? '已滿' : '報名' }}
                    </button>
            @endif
        </div>
    </div>
@endif
</div>
