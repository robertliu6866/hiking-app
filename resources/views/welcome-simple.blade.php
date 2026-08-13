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
                    先許願找同行，人夠多再由主揪開團。加入會員後就能 +1 響應。
                </p>

                <div class="mt-5 space-y-3">
                    @forelse ($wishes as $wish)
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
