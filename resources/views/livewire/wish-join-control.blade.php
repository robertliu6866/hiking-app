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
    $costs = \App\Support\WishCostSummary::for($wish);
@endphp

<article
    class="ui-card cursor-pointer px-4 py-4"
    @click="if (! $event.target.closest('button, a, input, dialog, [data-member-profile]')) $wire.openDetails()"
    wire:key="wish-{{ $wish->id }}"
    x-data="{
        detailsOpen: $wire.entangle('detailsOpen').live,
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
        @if ($wish->guided_days >= 2)
            <span class="ui-chip-hope">{{ $wish->guided_days == 2 ? '兩天一夜' : $wish->guided_days.' 天行程' }}</span>
        @endif
        <span class="ui-chip-hope">
            {{ $wishSignal }}
        </span>
        <span class="ui-chip">
            {{ $wish->wished_date ? $wish->wished_date->format('Y/m/d') : '日期彈性' }}
        </span>
        @if ($routeModeLabel && ! ($wish->route_mode === 'custom' && $wish->guided_days >= 2))
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

    <div class="mt-3 flex items-center justify-between gap-3 text-sm">
        <p class="text-slate-600">嚮導費 <span class="font-semibold text-emerald-800">NT${{ number_format($costs['guide']) }}</span>／人 <span class="text-xs text-slate-400">＋車資另攤</span></p>
        <button type="button" wire:click="openDetails" class="min-h-11 shrink-0 font-semibold text-emerald-700" aria-haspopup="dialog">看看這團 →</button>
    </div>

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
    <dialog
        wire:ignore.self
        x-ref="detailsDialog"
        x-effect="if (detailsOpen && !$el.open) $el.showModal(); if (!detailsOpen && $el.open) $el.close()"
        @cancel.prevent="detailsOpen = false"
        @click.self="detailsOpen = false"
        @close="detailsOpen = false"
        aria-labelledby="wish-detail-title-{{ $wish->id }}"
        class="m-auto max-h-[90dvh] w-[calc(100%-2rem)] max-w-[430px] overflow-y-auto rounded-3xl border-0 bg-slate-50 p-0 text-slate-900 shadow-xl backdrop:bg-slate-950/50"
    >
        @if ($detailsOpen)
            <div class="sticky top-0 z-10 flex items-center justify-between gap-3 border-b border-slate-100 bg-white px-5 py-4">
                <div>
                    <p class="text-xs font-medium text-emerald-700">一起出發的細節</p>
                    <h2 id="wish-detail-title-{{ $wish->id }}" class="mt-1 text-xl font-semibold">{{ $wish->mountain }}</h2>
                </div>
                <button type="button" @click="detailsOpen = false" autofocus aria-label="關閉活動詳情" class="flex h-11 w-11 items-center justify-center rounded-full bg-slate-100 text-xl text-slate-600">×</button>
            </div>
            <div class="space-y-4 p-5">
                <div class="flex flex-wrap gap-2">
                    <span class="ui-chip">{{ $wish->wished_date?->format('Y/m/d') ?? '日期再一起討論' }}</span>
                    <span class="ui-chip">{{ $wish->guided_days >= 2 ? ($wish->guided_days == 2 ? '兩天一夜' : $wish->guided_days.' 天行程') : ($routeModeLabel ?? '路線待討論') }}</span>
                </div>

                <section class="rounded-2xl bg-white p-4" aria-label="主揪資歷">
                    <h3 class="text-sm font-semibold text-slate-900">這次誰帶路？</h3>
                    <div class="mt-3 flex items-center gap-3">
                        @if ($wish->user)
                            <img src="{{ $wish->user->avatar_url }}" alt="" class="h-12 w-12 rounded-full object-cover">
                        @endif
                        <div>
                            <p class="font-semibold">{{ $wish->user?->name ?? '山友' }} <span class="ml-1 text-xs font-normal text-emerald-700">主揪</span></p>
                            <p class="mt-1 text-sm text-slate-500">登山年資：{{ $wish->user?->hiking_experience ?: '主揪尚未填寫' }}</p>
                        </div>
                    </div>
                    <p class="mt-3 text-sm text-slate-600">在這裡發起過 {{ $hostedCount }} 團</p>
                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $wish->user?->bio ?: '主揪還沒留下自我介紹。' }}</p>
                </section>

                <section class="rounded-2xl border border-emerald-100 bg-white p-4" aria-label="活動費用">
                    <h3 class="font-semibold">這趟大約花多少？</h3>
                    <dl class="mt-3 space-y-3 text-sm">
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">嚮導費</dt><dd class="font-semibold">NT${{ number_format($costs['guide']) }}／人</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">整車車資預估</dt><dd>NT${{ number_format($costs['transport']) }}／車</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">每人車資分攤</dt><dd>{{ $costs['share'] !== null ? 'NT$'.number_format($costs['share']) : '人數確認後計算' }}</dd></div>
                    </dl>
                    @if ($costs['sharingCount'])
                        <p class="mt-3 text-xs leading-5 text-slate-500">預計 {{ $wish->expected_participants }} 人{{ $costs['hostExempt'] ? '（含主揪），主揪免車資，由其餘 '.$costs['sharingCount'].' 位同行者均分。' : '，共同分攤車資。' }}</p>
                    @elseif ($costs['hostExempt'])
                        <p class="mt-3 text-xs leading-5 text-slate-500">主揪免車資，由其餘同行者均分；人數待確認。</p>
                    @endif
                    <div class="mt-4 rounded-xl bg-emerald-50 px-3 py-3">
                        <p class="text-xs text-emerald-700">每位同行者預估・嚮導費＋車資</p>
                        <p class="mt-1 text-xl font-semibold text-emerald-900">{{ $costs['total'] !== null ? 'NT$'.number_format($costs['total']) : 'NT$'.number_format($costs['guide']).' ＋車資另攤' }}</p>
                    </div>
                    <p class="mt-3 text-xs leading-5 text-slate-500">嚮導費每人每趟固定 NT${{ number_format($costs['guide']) }}，不隨天數增加。車資為預估，依實際報價與成團人數調整；住宿、餐食及其他費用若有，另由主揪說明。</p>
                </section>

                <section class="rounded-2xl bg-white p-4" aria-label="已參加成員列表">
                    <h3 class="font-semibold">這些山友也會來 <span class="text-sm font-normal text-slate-500">{{ $wish->users_count }} 人</span></h3>
                    <ul class="mt-3 divide-y divide-slate-100">
                        @forelse ($wish->users as $member)
                            <li class="flex items-center gap-3 py-3" wire:key="detail-member-{{ $wish->id }}-{{ $member->id }}">
                                <img src="{{ $member->avatar_url }}" alt="" class="h-10 w-10 rounded-full object-cover">
                                <span class="min-w-0 break-words text-sm font-medium">{{ $member->name }}</span>
                                @if ($member->id === $wish->user_id)<span class="ml-auto shrink-0 text-xs text-emerald-700">主揪</span>@endif
                            </li>
                        @empty
                            <li class="py-3 text-sm text-slate-500">還沒有山友加入，來當第一位吧。</li>
                        @endforelse
                    </ul>
                </section>

                <section class="rounded-2xl bg-white p-4" aria-label="行程介紹">
                    <h3 class="font-semibold">關於這趟旅程</h3>
                    <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-600">{{ $wish->note ?: '主揪還在準備行程介紹，集合時間與路線會再一起確認。' }}</p>
                </section>
            </div>
            <div class="sticky bottom-0 border-t border-slate-100 bg-white px-5 py-4">
                <button type="button" wire:click="toggle" wire:loading.attr="disabled" wire:target="toggle" @disabled($hasJoined && ! $allowCancel) class="ui-btn-primary w-full disabled:opacity-60">{{ $hasJoined ? ($allowCancel ? '取消參加' : '已參加') : '我也想一起去 ＋1' }}</button>
            </div>
        @endif
    </dialog>
</article>
