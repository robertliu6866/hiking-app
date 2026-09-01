<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-slate-900">登山許願</h2>
            <a href="{{ route('dashboard') }}" class="text-sm text-slate-500">我的許願</a>
        </div>
    </x-slot>

    <div
        class="page-shell"
        x-data="{
            createOpen: @js($errors->any()),
            query: @js(old('mountain', '')),
            suggestions: @js($mountainSuggestions),
            suggestionOpen: false,
            highlightedIndex: 0,
            tripMode: @js(old('homepage_group', 'self')),
            routeMode: @js(old('route_mode', '')),
            guidedDays: @js((int) old('guided_days', 1)),
            expectedParticipants: @js((int) old('expected_participants', 8)),
            guideDailyFee: @js(config('wishes.guide_daily_fee')),
            transportFee: @js(config('wishes.transport_fee')),
            get estimatedPerPerson() {
                const days = Math.max(1, Number(this.guidedDays) || 1);
                const people = Math.max(1, Number(this.expectedParticipants) || 1);

                return Math.ceil(((this.guideDailyFee * days) + this.transportFee) / people);
            },
            get selfTransportShare() {
                const people = Math.max(2, Number(this.expectedParticipants) || 2);

                return Math.ceil(this.transportFee / (people - 1));
            },
            get filteredSuggestions() {
                const keyword = this.query.trim().toLowerCase();

                if (! keyword) {
                    return this.suggestions.slice(0, 8);
                }

                return this.suggestions
                    .filter((item) => item.toLowerCase().includes(keyword))
                    .slice(0, 8);
            },
            chooseSuggestion(value) {
                this.query = value;
                this.suggestionOpen = false;
                this.highlightedIndex = 0;
            },
            moveSelection(step) {
                if (! this.suggestionOpen || this.filteredSuggestions.length === 0) {
                    this.suggestionOpen = true;
                    return;
                }

                this.highlightedIndex = (this.highlightedIndex + step + this.filteredSuggestions.length) % this.filteredSuggestions.length;
            },
            confirmSelection() {
                if (this.suggestionOpen && this.filteredSuggestions[this.highlightedIndex]) {
                    this.chooseSuggestion(this.filteredSuggestions[this.highlightedIndex]);
                }
            }
        }"
        x-on:keydown.escape.window="createOpen = false"
        x-effect="document.body.classList.toggle('overflow-hidden', createOpen)"
    >
        <div class="page-container space-y-5 pb-24">
            <section class="overflow-hidden rounded-3xl bg-[linear-gradient(135deg,#064e3b_0%,#047857_58%,#34d399_100%)] p-5 text-white shadow-sm shadow-emerald-200">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white">許願看板</span>
                        <h1 class="mt-3 text-2xl font-semibold leading-tight">找同樣想上山的人</h1>
                        <p class="mt-2 text-sm leading-6 text-emerald-50">每個許願都是一趟想出發的行程，看到想去的山就 +1。</p>
                    </div>
                    <div class="shrink-0 rounded-2xl bg-white/15 px-3 py-2 text-center backdrop-blur-sm">
                        <div class="text-lg font-semibold">{{ $totalParticipants }}</div>
                        <div class="text-[11px] text-emerald-50">位想去</div>
                    </div>
                </div>
            </section>

            <section class="space-y-3">
                <div class="flex items-end justify-between px-1">
                    <div>
                        <h3 class="text-base font-semibold text-slate-950">大家正在許願</h3>
                        <p class="mt-1 text-xs text-slate-500">{{ $totalMountains }} 個想去的地方 · 依出發日期排列</p>
                    </div>
                    @if ($nextWishDate)
                        <span class="ui-chip-hope">最近 {{ $nextWishDate->format('m/d') }}</span>
                    @endif
                </div>

                @forelse ($wishes as $wish)
                    @livewire(\App\Livewire\WishJoinControl::class, [
                        'wishId' => $wish->id,
                        'allowCancel' => true,
                        'simpleJoinLabel' => true,
                    ], key('popular-lottery-'.$wish->id))
                @empty
                    <div class="ui-empty">
                        <div class="text-sm font-medium text-slate-900">還沒有人發起許願</div>
                        <p class="mt-1 text-sm text-slate-500">成為第一位，告訴山友你想去哪裡。</p>
                        <button type="button" class="ui-btn-primary mt-4" x-on:click="createOpen = true">發起第一個許願</button>
                    </div>
                @endforelse

                @if ($wishes->hasPages())
                    <div class="ui-card px-4 py-3">
                        {{ $wishes->links() }}
                    </div>
                @endif
            </section>
        </div>

        <div class="pointer-events-none fixed inset-x-0 bottom-5 z-30 mx-auto flex w-full max-w-[430px] justify-center px-4">
            <button
                type="button"
                class="pointer-events-auto inline-flex min-h-12 items-center gap-2 rounded-full bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-900/25 transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:ring-offset-2"
                x-on:click="createOpen = true"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                發起許願
            </button>
        </div>

        <div x-cloak x-show="createOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-950/45" x-on:click="createOpen = false"></div>

        <section
            x-cloak
            x-show="createOpen"
            x-transition:enter="transition ease-out duration-250"
            x-transition:enter-start="translate-y-full"
            x-transition:enter-end="translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-y-0"
            x-transition:leave-end="translate-y-full"
            class="fixed inset-x-0 bottom-0 z-50 mx-auto w-full max-w-[430px] rounded-t-[2rem] bg-white shadow-2xl"
            role="dialog"
            aria-modal="true"
            aria-labelledby="create-wish-title"
        >
            <div class="max-h-[85vh] overflow-y-auto overscroll-contain px-5 pb-6">
                <div class="sticky top-0 z-10 -mx-5 border-b border-slate-100 bg-white/95 px-5 pb-3 pt-3 backdrop-blur">
                    <div class="mx-auto h-1.5 w-10 rounded-full bg-slate-200"></div>
                    <div class="mt-3 flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold text-emerald-700">發起新願望</p>
                            <h3 id="create-wish-title" class="mt-1 text-lg font-semibold text-slate-950">你想去哪座山？</h3>
                        </div>
                        <button type="button" class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-500" x-on:click="createOpen = false" aria-label="關閉發起許願表單">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 6 12 12M18 6 6 18" /></svg>
                        </button>
                    </div>
                </div>

                <p class="mt-4 text-sm leading-6 text-slate-500">填好山名與日期，其他山友就能直接找到你並 +1 響應。</p>

                <form method="POST" action="{{ route('trip-wishes.store') }}" class="mt-5 space-y-4">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ route('lotteries.yushan') }}">

                    <div class="space-y-2">
                        <label for="wish-mountain" class="ui-label">山名</label>
                        <div class="relative">
                            <input
                                id="wish-mountain"
                                name="mountain"
                                type="text"
                                x-model="query"
                                x-on:focus="suggestionOpen = true"
                                x-on:input="suggestionOpen = true; highlightedIndex = 0"
                                x-on:keydown.arrow-down.prevent="moveSelection(1)"
                                x-on:keydown.arrow-up.prevent="moveSelection(-1)"
                                x-on:keydown.enter.prevent="confirmSelection()"
                                x-on:click.outside="suggestionOpen = false"
                                placeholder="搜尋山名，例如：雪山主峰"
                                autocomplete="off"
                                class="w-full pr-11"
                                required
                            >
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" /></svg>
                            </div>
                            <div x-cloak x-show="suggestionOpen && filteredSuggestions.length > 0" x-transition class="absolute left-0 right-0 z-20 mt-2 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-lg shadow-slate-200/60">
                                <template x-for="(item, index) in filteredSuggestions" :key="item">
                                    <button type="button" x-on:click="chooseSuggestion(item)" :class="index === highlightedIndex ? 'bg-emerald-50 text-emerald-700' : 'bg-white text-slate-700'" class="flex w-full items-center justify-between px-4 py-3 text-left text-sm transition">
                                        <span x-text="item"></span>
                                        <span class="text-xs text-slate-400">熱門山岳</span>
                                    </button>
                                </template>
                            </div>
                        </div>
                        @error('mountain') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="wish-date" class="ui-label">可選日期</label>
                            <input id="wish-date" name="wished_date" type="date" value="{{ old('wished_date') }}" min="{{ $minWishDate }}">
                            @error('wished_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            <p class="ui-help">最早 {{ \Illuminate\Support\Carbon::parse($minWishDate)->format('Y/m/d') }}</p>
                        </div>

                        <div @if (! $supportsRouteMode) class="hidden" @endif>
                            <label for="wish-route-mode" class="ui-label">路線型態</label>
                            <select id="wish-route-mode" name="route_mode" x-model="routeMode">
                                <option value="">尚未決定</option>
                                <option value="single" @selected(old('route_mode') === 'single')>單攻</option>
                                <option value="traverse" @selected(old('route_mode') === 'traverse')>縱走</option>
                                <option value="custom" @selected(old('route_mode') === 'custom')>其他</option>
                            </select>
                            @error('route_mode') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <fieldset>
                        <legend class="ui-label">這趟怎麼成行？</legend>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="cursor-pointer rounded-2xl border p-3 transition" :class="tripMode === 'guided' ? 'border-emerald-500 bg-emerald-50' : 'border-slate-200 bg-white'">
                                <input class="sr-only" type="radio" name="homepage_group" value="guided" x-model="tripMode">
                                <span class="block text-sm font-semibold text-slate-900">請嚮導帶團</span>
                                <span class="mt-1 block text-xs leading-5 text-slate-500">費用依天數與人數均攤</span>
                            </label>
                            <label class="cursor-pointer rounded-2xl border p-3 transition" :class="tripMode === 'self' ? 'border-emerald-500 bg-emerald-50' : 'border-slate-200 bg-white'">
                                <input class="sr-only" type="radio" name="homepage_group" value="self" x-model="tripMode">
                                <span class="block text-sm font-semibold text-slate-900">自由成團</span>
                                <span class="mt-1 block text-xs leading-5 text-slate-500">成行時抽籤選協調人</span>
                            </label>
                        </div>
                        @error('homepage_group') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </fieldset>

                    <div x-cloak x-show="tripMode === 'guided'" x-transition class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h4 class="text-sm font-semibold text-emerald-950">嚮導與車費均攤</h4>
                                <p class="mt-1 text-xs leading-5 text-emerald-800">嚮導 NT$4,000／天，車費 NT$8,000／車。</p>
                            </div>
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-emerald-700">預估 <span x-text="'NT$' + estimatedPerPerson.toLocaleString()"></span>／人</span>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div>
                                <label for="guided-days" class="ui-label">行程天數</label>
                                <input id="guided-days" name="guided_days" type="number" min="1" max="14" x-model.number="guidedDays" x-bind:required="tripMode === 'guided'">
                                @error('guided_days') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="expected-participants" class="ui-label">揪團人數</label>
                                <input id="expected-participants" name="expected_participants" type="number" min="2" max="30" x-model.number="expectedParticipants" x-bind:required="tripMode === 'guided'">
                                @error('expected_participants') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <p class="mt-3 text-xs leading-5 text-emerald-800">計算方式：〈NT$4,000 × 天數＋NT$8,000 車費〉÷ 揪團人數。實際費用以成行確認為準。</p>
                    </div>

                    <div x-cloak x-show="tripMode === 'self' && routeMode === 'single'" x-transition class="rounded-2xl border border-amber-100 bg-amber-50 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h4 class="text-sm font-semibold text-amber-950">單攻共乘誘因</h4>
                                <p class="mt-1 text-xs leading-5 text-amber-800">整車車資預設 NT$8,000；主揪免車資，其餘同行者均分。</p>
                            </div>
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-amber-800">每位同行者預估 <span x-text="'NT$' + selfTransportShare.toLocaleString()"></span></span>
                        </div>
                        <div class="mt-4">
                            <label for="self-expected-participants" class="ui-label">預計總人數 <span class="font-normal text-slate-400">（含主揪）</span></label>
                            <input id="self-expected-participants" name="expected_participants" type="number" min="2" max="30" x-model.number="expectedParticipants" x-bind:required="tripMode === 'self' && routeMode === 'single'">
                            @error('expected_participants') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <p class="mt-3 text-xs leading-5 text-amber-800">計算方式：NT$8,000 ÷（總人數 − 1 位主揪）。實際金額依最後成團人數調整。</p>
                    </div>

                    <div>
                        <label for="wish-note" class="ui-label">補充說明 <span class="font-normal text-slate-400">（選填）</span></label>
                        <textarea id="wish-note" name="note" rows="3" placeholder="例如：想找七月平日、可自行開車、希望慢慢走">{{ old('note') }}</textarea>
                        @error('note') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="ui-btn-primary w-full">發布許願</button>
                </form>
            </div>
        </section>
    </div>
</x-app-layout>
