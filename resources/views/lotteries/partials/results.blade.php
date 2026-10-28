            <section class="space-y-3" aria-labelledby="trip-results-title">
                <div class="flex items-end justify-between px-1">
                    <div>
                        <h3 id="trip-results-title" class="text-base font-semibold text-slate-950">{{ $hasFilters ? '這幾團，想一起嗎？' : '大家正在許願' }}</h3>
                        <p class="mt-1 text-xs text-slate-500">{{ $wishes->total() }} 團等你一起出發 · {{ ($filters['sort'] ?? 'date') === 'popular' ? '依響應人數排列' : '依出發日期排列' }}</p>
                        <p class="mt-1 text-xs text-slate-500">顯示 {{ \Illuminate\Support\Carbon::parse($minWishDate)->format('m/d') }} 起出發的行程</p>
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
                        'detailsOpen' => ($selectedWishId ?? null) === $wish->id,
                    ], key('popular-lottery-'.$wish->id))
                @empty
                    <div class="ui-empty">
                        @if ($hasFilters)
                            <div class="text-sm font-medium text-slate-900">目前沒有符合條件的行程</div>
                            <p class="mt-1 text-sm text-slate-500">試試其他月份、縮短山名，或放寬行程類型。</p>
                            <a href="{{ route('lotteries.yushan') }}" class="ui-btn-soft mt-4">查看全部行程</a>
                        @else
                            <div class="text-sm font-medium text-slate-900">還沒有人發起許願</div>
                            <p class="mt-1 text-sm text-slate-500">成為第一位，告訴山友你想去哪裡。</p>
                            <button type="button" class="ui-btn-primary mt-4" x-on:click="createOpen = true">發起第一個許願</button>
                        @endif
                    </div>
                @endforelse

                @if ($wishes->hasPages())
                    <div class="ui-card px-4 py-3">
                        {{ $wishes->links('pagination.compact') }}
                    </div>
                @endif
            </section>
