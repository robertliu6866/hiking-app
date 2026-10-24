<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', '登山活動') }}</title>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-950">
        <x-guest-welcome-dialog />
        <main class="mx-auto flex min-h-screen w-full max-w-[430px] flex-col px-5 py-6">
            <header class="flex items-center gap-3">
                <x-application-logo class="h-11 w-11 shrink-0" />
                <div>
                    <p class="text-[15px] font-semibold text-slate-950">劉里長登山社</p>
                    <p class="mt-0.5 text-xs text-slate-500">找山友，輕鬆一起走。</p>
                </div>
            </header>

            <section class="py-8">
                <p class="text-sm font-semibold text-emerald-700">許願活動</p>
                <h1 class="mt-2 text-3xl font-semibold leading-tight tracking-tight text-slate-950">
                    看看大家最近<br>想一起走哪座山。
                </h1>
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    發布許願就是發起一趟想去的行程；加入會員後即可發布或 +1 響應。
                </p>

                <form method="GET" action="{{ route('lotteries.yushan') }}" class="mt-5 rounded-2xl border border-emerald-100 bg-white p-4">
                    <label for="guest-trip-search" class="ui-label">找符合需求的行程</label>
                    <input id="guest-trip-search" name="q" type="search" maxlength="100" placeholder="輸入山名或主揪" class="min-h-11 w-full rounded-2xl border-slate-200 px-4 py-3 text-sm focus:border-emerald-400 focus:ring-emerald-100">
                    <button type="submit" class="ui-btn-primary mt-3 w-full">登入並尋找行程</button>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <a href="{{ route('lotteries.yushan', ['duration' => 'single']) }}" class="ui-chip">找單攻團</a>
                        <a href="{{ route('lotteries.yushan', ['duration' => 'overnight']) }}" class="ui-chip">找過夜團</a>
                    </div>
                    <p class="mt-3 text-xs leading-5 text-slate-500">登入後可依月份、行程類型與成團方式篩選。</p>
                </form>

                <div class="mt-5 space-y-3">
                    @forelse ($wishes as $wish)
                        @php
                            $wishUsers = collect([$wish->user])
                                ->merge($wish->users)
                                ->filter()
                                ->unique('id')
                                ->take(6);
                        @endphp
                        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/60">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-lg font-semibold text-slate-950">{{ $wish->mountain }}</p>
                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ $wish->wished_date?->format('m/d') ?? '日期待定' }} · {{ $wish->user?->name ?? '山友' }} 發起
                                    </p>
                                </div>
                                <span class="shrink-0 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                    {{ max(1, $wish->users_count) }} 人想去
                                </span>
                            </div>
                            @if ($wish->note)
                                <p class="mt-3 text-sm leading-6 text-slate-600">{{ $wish->note }}</p>
                            @endif
                            <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
                                <div>
                                    <p class="text-xs text-slate-500">想去的山友</p>
                                    <div class="mt-2 flex items-center">
                                        @foreach ($wishUsers as $wishUser)
                                            <img
                                                src="{{ $wishUser->avatar_url }}"
                                                alt="{{ $wishUser->name }}"
                                                title="{{ $wishUser->name }}"
                                                class="-ml-1 h-8 w-8 rounded-full border-2 border-white object-cover first:ml-0"
                                            >
                                        @endforeach
                                    </div>
                                </div>
                                @if ($wish->users_count > $wishUsers->count())
                                    <span class="text-xs text-slate-500">還有 {{ $wish->users_count - $wishUsers->count() }} 位</span>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-emerald-200 bg-emerald-50 p-5">
                            <p class="font-semibold text-slate-900">第一個許願，等你發起</p>
                            <p class="mt-1 text-sm leading-6 text-slate-600">加入會員後，選一座想去的山，邀請山友一起湊團。</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/70">
                <a href="{{ route('register') }}" class="ui-btn-primary w-full">
                    加入會員，許願 +1
                </a>
                <p class="mt-3 text-center text-sm text-slate-500">
                    已經是會員？
                    <a href="{{ route('login') }}" class="font-semibold text-emerald-700 underline underline-offset-4">登入</a>
                </p>
            </section>
        </main>
    </body>
</html>
