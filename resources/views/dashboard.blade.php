<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-slate-900">我的揪團</h2>
            <a href="{{ route('lotteries.yushan') }}" class="text-sm font-medium text-emerald-700">查看揪團池</a>
        </div>
    </x-slot>

    <div class="page-shell">
        <div class="page-container space-y-4">
            <section class="ui-card">
                <p class="text-sm font-semibold text-emerald-700">會員中心</p>
                <h1 class="mt-1 text-2xl font-semibold leading-tight text-slate-950">我的揪團</h1>
                <p class="mt-2 text-sm leading-6 text-slate-500">
                    這裡會列出你發起或表態同行的社群揪團。
                </p>
            </section>

            <section class="space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">已表態的揪團</h2>
                        <p class="mt-1 text-sm text-slate-500">你表態同行的揪團會顯示在這裡。</p>
                    </div>
                </div>

                @forelse ($joinedWishes as $wish)
                    <article class="ui-card">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-xs font-semibold text-emerald-700">
                                    {{ $wish->wished_date ? $wish->wished_date->format('Y/m/d') : '日期彈性' }}
                                </div>
                                <h3 class="mt-1 text-lg font-semibold leading-tight text-slate-950">{{ $wish->mountain }}</h3>
                                <p class="mt-1 text-sm leading-6 text-slate-500">
                                    {{ $wish->user?->name ?? '山友' }} 發起
                                    @if ($wish->homepage_group === 'guided')
                                        · 有人可帶
                                    @elseif ($wish->homepage_group === 'self')
                                        · 自由成團
                                    @endif
                                </p>
                            </div>

                            <span class="shrink-0 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                已表態
                            </span>
                        </div>

                        @if ($wish->note)
                            <p class="mt-3 text-sm leading-6 text-slate-600">{{ $wish->note }}</p>
                        @endif

                        <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
                            <div class="min-w-0">
                                <div class="text-xs text-slate-500">一起表態的山友</div>
                                <div class="mt-2 flex items-center">
                                    @foreach ($wish->users->take(5) as $participant)
                                        <div class="-ml-1 first:ml-0">
                                            <x-member-profile-popover :user="$participant" />
                                        </div>
                                    @endforeach

                                    @if ($wish->users_count > 5)
                                        <span class="ml-2 text-xs text-slate-500">+{{ $wish->users_count - 5 }}</span>
                                    @endif
                                </div>
                            </div>

                            <span class="text-xs text-slate-500">{{ $wish->users_count }} 人表態</span>
                        </div>
                    </article>
                @empty
                    <div class="ui-empty">
                        <div class="text-sm font-medium text-slate-900">尚未加入揪團</div>
                        <p class="mt-1 text-sm text-slate-500">看到想去的山先表態同行，後續山友會顯示在這裡。</p>
                    </div>
                @endforelse
            </section>
        </div>
    </div>
</x-app-layout>
