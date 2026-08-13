<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-slate-900">登山許願</h2>
            <a href="{{ route('dashboard') }}" class="text-sm text-slate-500">我的許願</a>
        </div>
    </x-slot>

    <div class="page-shell">
        <div class="page-container space-y-4">
            <section class="ui-card overflow-hidden">
                <div class="-mx-5 -mt-5 h-48 bg-[linear-gradient(135deg,#0f172a_0%,#334155_42%,#dbeafe_100%)]">
                    <div class="flex h-full items-end bg-black/10 p-5">
                        <div>
                            <span class="rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-slate-800">以山友為中心</span>
                            <h1 class="mt-3 text-3xl font-semibold leading-tight text-white">每一個許願，就是一趟想去的行程</h1>
                            <p class="mt-2 text-sm leading-6 text-white/85">發起你的登山願望，找到同樣想去的山友，一起把想法變成出發計畫。</p>
                        </div>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-3 gap-2">
                    <div class="rounded-2xl border border-slate-100 px-3 py-3 text-center">
                        <div class="text-lg font-semibold text-slate-950">{{ $totalParticipants }}</div>
                        <div class="mt-1 text-xs text-slate-500">已 +1</div>
                    </div>
                    <div class="rounded-2xl border border-slate-100 px-3 py-3 text-center">
                        <div class="text-lg font-semibold text-slate-950">{{ $nextWishDate ? $nextWishDate->format('m/d') : '--' }}</div>
                        <div class="mt-1 text-xs text-slate-500">最早出發</div>
                    </div>
                    <div class="rounded-2xl border border-slate-100 px-3 py-3 text-center">
                        <div class="text-lg font-semibold text-emerald-700">5</div>
                        <div class="mt-1 text-xs text-slate-500">每頁顯示</div>
                    </div>
                </div>

                <div class="mt-5 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm leading-6 text-emerald-800">
                    不需要等待主揪開團：你發布的許願本身就是活動。選好山、日期和路線，讓其他山友直接響應。
                </div>
            </section>

            <section
                class="ui-card space-y-4"
                x-data="{
                    query: '{{ old('mountain', '') }}',
                    open: false,
                    highlightedIndex: 0,
                    suggestions: @js($mountainSuggestions),
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
                        this.open = false;
                        this.highlightedIndex = 0;
                    },
                    moveSelection(step) {
                        if (! this.open || this.filteredSuggestions.length === 0) {
                            this.open = true;
                            return;
                        }

                        this.highlightedIndex = (this.highlightedIndex + step + this.filteredSuggestions.length) % this.filteredSuggestions.length;
                    },
                    confirmSelection() {
                        if (this.open && this.filteredSuggestions[this.highlightedIndex]) {
                            this.chooseSuggestion(this.filteredSuggestions[this.highlightedIndex]);
                        }
                    }
                }"
            >
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="ui-section-title">先建立你的許願</h3>
                        <p class="ui-muted mt-1">先把想去的山排進清單。山名可直接搜尋，日期至少從五天後開始，路線型態也能先標註。</p>
                    </div>
                    <span class="ui-chip">Typeahead Search</span>
                </div>

                <form method="POST" action="{{ route('trip-wishes.store') }}" class="space-y-4">
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
                                x-on:focus="open = true"
                                x-on:input="open = true; highlightedIndex = 0"
                                x-on:keydown.arrow-down.prevent="moveSelection(1)"
                                x-on:keydown.arrow-up.prevent="moveSelection(-1)"
                                x-on:keydown.enter.prevent="confirmSelection()"
                                x-on:click.outside="open = false"
                                value="{{ old('mountain') }}"
                                placeholder="搜尋山名，例如：雪山主峰"
                                autocomplete="off"
                                class="w-full pr-11"
                                required
                            >
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                                </svg>
                            </div>

                            <div
                                x-cloak
                                x-show="open && filteredSuggestions.length > 0"
                                x-transition
                                class="absolute left-0 right-0 z-10 mt-2 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-lg shadow-slate-200/60"
                            >
                                <template x-for="(item, index) in filteredSuggestions" :key="item">
                                    <button
                                        type="button"
                                        x-on:click="chooseSuggestion(item)"
                                        :class="index === highlightedIndex ? 'bg-emerald-50 text-emerald-700' : 'bg-white text-slate-700'"
                                        class="flex w-full items-center justify-between px-4 py-3 text-left text-sm transition"
                                    >
                                        <span x-text="item"></span>
                                        <span class="text-xs text-slate-400">熱門山岳</span>
                                    </button>
                                </template>
                            </div>
                        </div>
                        @error('mountain')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="ui-help">目前是站內即時搜尋體驗，之後可以直接換接真正的 Typesense 索引。</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="wish-date" class="ui-label">可選日期</label>
                            <input id="wish-date" name="wished_date" type="date" value="{{ old('wished_date') }}" min="{{ $minWishDate }}">
                            @error('wished_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="ui-help">最早可選 {{ \Illuminate\Support\Carbon::parse($minWishDate)->format('Y/m/d') }}</p>
                        </div>

                        <div @if (! $supportsRouteMode) class="hidden" @endif>
                            <label for="wish-route-mode" class="ui-label">路線型態</label>
                            <select id="wish-route-mode" name="route_mode">
                                <option value="">尚未決定</option>
                                <option value="single" @selected(old('route_mode') === 'single')>單攻</option>
                                <option value="traverse" @selected(old('route_mode') === 'traverse')>縱走</option>
                                <option value="custom" @selected(old('route_mode') === 'custom')>其他</option>
                            </select>
                            @error('route_mode')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="wish-note" class="ui-label">補充說明</label>
                        <textarea
                            id="wish-note"
                            name="note"
                            rows="3"
                            placeholder="例如：想找七月平日、可自行開車、希望慢慢走"
                        >{{ old('note') }}</textarea>
                        @error('note')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="ui-btn-primary w-full">送出許願</button>
                </form>
            </section>

            <section class="space-y-3">
                <div class="flex items-center justify-between px-1">
                    <h3 class="text-sm font-semibold text-slate-900">已發起的許願</h3>
                    <span class="text-xs font-medium text-slate-500">{{ $totalMountains }} 筆已排進清單</span>
                </div>

                @forelse ($wishes as $wish)
                    @livewire(\App\Livewire\WishJoinControl::class, [
                        'wishId' => $wish->id,
                        'allowCancel' => true,
                        'simpleJoinLabel' => true,
                    ], key('popular-lottery-'.$wish->id))
                @empty
                    <div class="ui-empty">
                        <div class="text-sm font-medium text-slate-900">目前還沒有符合條件的許願</div>
                        <p class="mt-1 text-sm text-slate-500">有人發起五天後的許願後，就會出現在這裡。</p>
                    </div>
                @endforelse

                @if ($wishes->hasPages())
                    <div class="ui-card px-4 py-3">
                        {{ $wishes->links() }}
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
