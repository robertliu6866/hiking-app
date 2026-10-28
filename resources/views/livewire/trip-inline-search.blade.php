<div class="relative" x-data="{
    open: false,
    highlighted: -1,
    options() {
        return this.$refs.list?.dataset.query === this.$refs.input.value.trim()
            ? Array.from(this.$refs.list.querySelectorAll('[data-search-result]')) : [];
    },
    move(step) {
        this.open = true;
        const items = this.options();
        if (!items.length) return;
        this.highlighted = Math.max(0, Math.min(items.length - 1, this.highlighted + step));
        this.$nextTick(() => items[this.highlighted]?.scrollIntoView({ block: 'nearest' }));
    },
    select(event) {
        if (event.isComposing || event.keyCode === 229) return;
        event.preventDefault();
        const selected = this.options()[this.highlighted];
        this.open = false;
        if (selected) selected.click();
        else this.$dispatch('trip-search-submit');
    },
    async clear() {
        this.highlighted = -1;
        this.open = true;
        this.$refs.input.value = '';
        await this.$wire.set('search', '');
        this.$refs.input.focus();
        this.$dispatch('trip-search-submit');
    }
}" @click.outside="open = false" @keydown.escape="if (!$event.isComposing) { $event.preventDefault(); $event.stopPropagation(); open = false; highlighted = -1 }"
    @focusout="if (! $el.contains($event.relatedTarget)) open = false">
    <label for="trip-search" class="sr-only">搜尋山名、主揪或行程內容</label>
    <div class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-white p-1.5 focus-within:border-emerald-400">
        <input id="trip-search" x-ref="input" name="q" type="search" maxlength="100" autocomplete="off"
            wire:model.live.debounce.300ms="search"
            @focus="open = true" @input="open = true; highlighted = -1"
            @keydown.arrow-down="if (!$event.isComposing) { $event.preventDefault(); move(1) }"
            @keydown.arrow-up="if (!$event.isComposing) { $event.preventDefault(); move(-1) }"
            @keydown.enter="select($event)"
            role="combobox" aria-autocomplete="list" aria-controls="trip-search-results"
            :aria-expanded="open" :aria-activedescendant="open && highlighted >= 0 ? 'trip-option-' + highlighted : null"
            placeholder="搜山名、主揪或行程內容"
            class="min-h-11 min-w-0 flex-1 rounded-xl border-0 bg-transparent px-3 text-sm focus:ring-2 focus:ring-emerald-200">
        @if ($search !== '')
            <button type="button" @click="clear()" aria-label="清空搜尋" class="min-h-11 px-2 text-slate-500">清除</button>
        @endif
        <button type="submit" @click="open = false" class="ui-btn-soft shrink-0">搜尋</button>
    </div>

    <div x-cloak x-show="open" x-transition.opacity.duration.150ms
        class="absolute inset-x-0 top-full z-30 mt-2 overflow-hidden rounded-2xl border border-emerald-100 bg-white shadow-xl shadow-slate-900/10">
        <p wire:offline role="status" class="px-4 py-3 text-sm text-red-600">目前沒有網路連線，連線恢復後請再搜尋。</p>
        <div wire:loading wire:target="search" role="status" class="px-4 py-3 text-sm text-emerald-700">正在找適合的行程…</div>
        @if (mb_strlen(trim($search)) < 2)
            <div class="p-4">
                @if ($recentSearches)
                    <div class="mb-3 flex items-center justify-between gap-2">
                        <p class="text-xs font-semibold text-slate-600">最近搜尋</p>
                        <button type="button" wire:click="clearRecent" class="min-h-9 text-xs text-slate-500">清除紀錄</button>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($recentSearches as $recent)
                            <button type="button" wire:click="useRecent(@js($recent))" @click="highlighted = -1; open = true; $refs.input.focus()" class="ui-chip min-h-10">{{ $recent }}</button>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-500">輸入至少 2 個字，找山名、主揪或行程內容。</p>
                @endif
            </div>
        @else
            <p class="border-b border-slate-100 px-4 py-2 text-xs text-slate-500" role="status">{{ $this->results->count() ? '找到 '.$this->results->count().' 筆行程，最多顯示 10 筆' : '目前沒有符合的行程，試試其他關鍵字或放寬篩選。' }}</p>
        @endif
        <ul id="trip-search-results" role="listbox" aria-label="行程搜尋結果" x-ref="list" data-query="{{ trim($search) }}" class="max-h-80 overflow-y-auto overscroll-contain">
            @foreach ($this->results as $result)
                <li role="option" id="trip-option-{{ $loop->index }}" :aria-selected="highlighted === {{ $loop->index }}" wire:key="inline-result-{{ $result->id }}">
                    <button type="button" data-search-result wire:click="choose({{ $result->id }})"
                        @mouseenter="highlighted = {{ $loop->index }}" @focus="highlighted = {{ $loop->index }}"
                        :class="highlighted === {{ $loop->index }} ? 'bg-emerald-50' : 'bg-white'"
                        class="block w-full border-b border-slate-100 px-4 py-3 text-left transition-colors hover:bg-emerald-50 focus:bg-emerald-50 focus:outline-none">
                        <span class="block text-sm font-semibold text-slate-900">{!! $this->highlight($result->mountain ?? '未命名行程') !!}</span>
                        <span class="mt-1 block text-xs text-slate-500">{{ $result->wished_date->format('Y/m/d') }} · {!! $this->highlight($result->user?->name ?? '山友') !!} · {{ $result->users_count }} 人想去</span>
                        @if ($result->note)
                            <span class="mt-1 block text-xs leading-5 text-slate-600">{!! $this->highlight($this->snippet($result->note)) !!}</span>
                        @endif
                    </button>
                </li>
            @endforeach
        </ul>
        <p class="border-t border-slate-100 bg-slate-50 px-4 py-2 text-xs text-slate-500">↑↓ 選擇 · Enter 查看 · Esc 關閉</p>
    </div>
</div>
