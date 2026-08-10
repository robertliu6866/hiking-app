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

            <section class="my-auto py-10">
                <p class="text-sm font-semibold text-emerald-700">從第一趟開始</p>
                <h1 class="mt-3 text-4xl font-semibold leading-tight tracking-tight text-slate-950">
                    找到下一趟，<br>想一起走的山。
                </h1>
                <p class="mt-4 max-w-sm text-base leading-7 text-slate-600">
                    會員登入後報名登山活動，查看活動資訊、報名同行，也能隨時掌握自己的行程。
                </p>

                <div class="mt-8 grid grid-cols-3 gap-3">
                    <div class="rounded-2xl border border-slate-200 bg-white px-3 py-4">
                        <span class="text-xs font-semibold text-emerald-700">01</span>
                        <p class="mt-2 text-sm font-semibold text-slate-800">加入會員</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white px-3 py-4">
                        <span class="text-xs font-semibold text-emerald-700">02</span>
                        <p class="mt-2 text-sm font-semibold text-slate-800">選擇活動</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white px-3 py-4">
                        <span class="text-xs font-semibold text-emerald-700">03</span>
                        <p class="mt-2 text-sm font-semibold text-slate-800">安心同行</p>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/70">
                <a href="{{ route('register') }}" class="ui-btn-primary w-full">
                    註冊會員
                </a>
                <p class="mt-3 text-center text-sm text-slate-500">
                    已經是會員？
                    <a href="{{ route('login') }}" class="font-semibold text-emerald-700 underline underline-offset-4">登入</a>
                </p>
            </section>
        </main>
    </body>
</html>
