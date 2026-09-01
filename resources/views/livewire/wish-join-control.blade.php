@php
    $hasJoined = $wish->users->contains(auth()->id());
    $otherUsers = $wish->users->where('id', '!=', auth()->id())->take(6);
    $routeModeLabel = match ($wish->route_mode) {
        'single' => '單攻',
        'traverse' => '縱走',
        'custom' => '其他',
        default => null,
    };
    $wishSignal = match (true) {
        $wish->users_count >= 5 => '熱門願望',
        $wish->users_count >= 2 => '正在聚人',
        default => '等待共鳴',
    };
    $guidedEstimate = $wish->homepage_group === 'guided' && $wish->guided_days && $wish->expected_participants
        ? (int) ceil(((config('wishes.guide_daily_fee') * $wish->guided_days) + config('wishes.transport_fee')) / $wish->expected_participants)
        : null;
    $selfTransportShare = $wish->homepage_group === 'self' && $wish->route_mode === 'single' && $wish->expected_participants >= 2
        ? (int) ceil(config('wishes.transport_fee') / ($wish->expected_participants - 1))
        : null;
@endphp

<article
    class="ui-card px-4 py-4"
    wire:key="wish-{{ $wish->id }}"
    x-data="{
        hasJoined: @js($hasJoined),
        count: @js($wish->users_count),
        allowCancel: @js($allowCancel),
        simpleJoinLabel: @js($simpleJoinLabel),
        notice: '',
        locked: false,
        buttonLabel() {
            if (this.hasJoined) {
                return this.allowCancel ? '取消' : '已完成';
            }

            return this.simpleJoinLabel ? '+1' : '+' + this.count;
        },
        showNotice(message = '已完成報名') {
            this.notice = message;
            setTimeout(() => this.notice = '', 2200);
        },
        joinFromAvatar() {
            if (this.locked) {
                return;
            }

            if (this.hasJoined) {
                this.showNotice('已完成報名');

                return;
            }

            this.hasJoined = true;
            this.count += 1;
            this.locked = true;
            this.showNotice('已完成報名');
            setTimeout(() => this.locked = false, 500);
        },
        toggleLocal() {
            if (this.locked) {
                return;
            }

            if (this.hasJoined && ! this.allowCancel) {
                return;
            }

            this.hasJoined = ! this.hasJoined;
            this.count = Math.max(0, this.count + (this.hasJoined ? 1 : -1));
            this.locked = true;
            setTimeout(() => this.locked = false, 500);
        },
        syncFromServer(detail) {
            if (detail.wishId !== @js($wish->id)) {
                return;
            }

            this.hasJoined = detail.hasJoined;
            this.count = detail.count;
            this.locked = false;
        }
    }"
    x-on:wish-notice.window="
        if ($event.detail.wishId === @js($wish->id)) {
            showNotice($event.detail.message);
        }
    "
    x-on:wish-join-updated.window="syncFromServer($event.detail)"
>
    <div class="flex flex-wrap items-center gap-2">
        <span class="ui-chip-hope">
            {{ $wishSignal }}
        </span>
        <span class="ui-chip">
            {{ $wish->wished_date ? $wish->wished_date->format('Y/m/d') : '日期彈性' }}
        </span>
        @if ($routeModeLabel)
            <span class="ui-chip">
                {{ $routeModeLabel }}
            </span>
        @endif
        @if ($wish->homepage_group === 'guided')
            <span class="ui-chip-hope">請嚮導帶團</span>
        @elseif ($wish->homepage_group === 'self')
            <span class="ui-chip">自由成團</span>
        @endif
    </div>

    <div class="mt-3 flex items-start justify-between gap-3">
        <div class="min-w-0">
            <div class="text-lg font-semibold leading-tight text-slate-950">
                {{ $wish->mountain }}
            </div>
            <div class="mt-1 text-xs text-slate-500">
                {{ $wish->user?->name ?? '山友' }} 發起 · <span x-text="count">{{ $wish->users_count }}</span> 人想去
            </div>
        </div>

        <button
            type="button"
            @click.prevent.stop="toggleLocal(); $wire.toggle()"
            :class="hasJoined ? 'ui-btn-primary' : 'ui-btn-soft'"
            :disabled="locked || (hasJoined && ! allowCancel)"
            class="inline-flex h-9 min-h-9 w-24 px-3 py-1.5 disabled:cursor-wait disabled:opacity-70"
        >
            <span x-text="buttonLabel()">
                {{ $hasJoined ? ($allowCancel ? '取消' : '已完成') : ($simpleJoinLabel ? '+1' : '+'.$wish->users_count) }}
            </span>
        </button>
    </div>

    @if ($wish->note)
        <p class="mt-3 rounded-2xl bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-600">
            {{ $wish->note }}
        </p>
    @endif

    @if ($guidedEstimate)
        <div class="mt-3 grid grid-cols-2 divide-x divide-emerald-100 rounded-2xl border border-emerald-100 bg-emerald-50 text-sm">
            <div class="px-4 py-3">
                <div class="text-xs font-medium text-emerald-700">揪團人數</div>
                <div class="mt-1 font-semibold text-emerald-950">{{ $wish->expected_participants }} 人 <span class="text-xs font-normal text-emerald-700">· {{ $wish->guided_days }} 天</span></div>
            </div>
            <div class="px-4 py-3">
                <div class="text-xs font-medium text-emerald-700">每人預估費用</div>
                <div class="mt-1 font-semibold text-emerald-950">NT${{ number_format($guidedEstimate) }}</div>
                <div class="mt-0.5 text-xs text-emerald-700">嚮導＋車費均攤</div>
            </div>
        </div>
    @endif

    @if ($selfTransportShare)
        <div class="mt-3 grid grid-cols-2 divide-x divide-amber-100 rounded-2xl border border-amber-100 bg-amber-50 text-sm">
            <div class="px-4 py-3">
                <div class="text-xs font-medium text-amber-700">單攻整車車資</div>
                <div class="mt-1 font-semibold text-amber-950">NT$8,000</div>
                <div class="mt-0.5 text-xs text-amber-700">主揪免車資</div>
            </div>
            <div class="px-4 py-3">
                <div class="text-xs font-medium text-amber-700">{{ $wish->expected_participants - 1 }} 位同行者均分</div>
                <div class="mt-1 font-semibold text-amber-950">每人 NT${{ number_format($selfTransportShare) }}</div>
                <div class="mt-0.5 text-xs text-amber-700">總共 {{ $wish->expected_participants }} 人，含主揪</div>
            </div>
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between gap-3 border-t border-slate-100 pt-3">
        <div class="flex min-h-7 items-center">
            <button
                x-cloak
                x-show="! hasJoined"
                x-transition.opacity.scale.90
                type="button"
                @click.prevent.stop="joinFromAvatar(); $wire.toggle()"
                :disabled="locked"
                class="-ml-1 first:ml-0 relative flex h-7 w-7 items-center justify-center rounded-full border-2 border-white bg-emerald-50 text-xs font-semibold text-emerald-700 shadow-sm disabled:cursor-wait disabled:opacity-70"
                title="點頭像完成報名"
                aria-label="點頭像完成報名"
            >
                {{ mb_substr(auth()->user()->name, 0, 1) }}
                <span class="absolute -right-0.5 -top-0.5 flex h-3.5 w-3.5 items-center justify-center rounded-full bg-emerald-600 text-[10px] leading-none text-white">+</span>
            </button>

            <div
                x-cloak
                x-show="hasJoined"
                x-transition.opacity.scale.90
                class="-ml-1 first:ml-0"
            >
                <x-member-profile-popover :user="auth()->user()" />
            </div>

            @foreach ($otherUsers as $user)
                <div
                    @if ($loop->iteration === 6)
                        x-show="! hasJoined"
                        x-transition.opacity.scale.90
                    @endif
                    class="-ml-1 first:ml-0"
                >
                    <x-member-profile-popover :user="$user" />
                </div>
            @endforeach

            <span
                x-cloak
                x-show="count > 6"
                x-text="'+' + (count - 6)"
                class="ml-2 text-xs text-slate-500"
            ></span>
        </div>

        <div class="flex shrink-0 items-center gap-2">
            <span class="text-xs font-medium text-slate-500" x-text="count + ' 人響應'">
                {{ $wish->users_count }} 人響應
            </span>

        </div>
    </div>

    <div
        x-cloak
        x-show="notice"
        x-transition
        class="mt-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700"
        x-text="notice"
    ></div>
</article>
