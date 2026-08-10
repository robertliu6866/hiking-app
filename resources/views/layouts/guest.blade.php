<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased">
        <div class="mx-auto min-h-screen w-full max-w-[430px] bg-gradient-to-b from-emerald-50 via-slate-50 to-white">
            <header class="flex items-center justify-between px-5 py-5">
                <a href="/" class="flex items-center gap-3">
                    <x-application-logo class="h-11 w-11 shrink-0" />
                    <span>
                        <span class="block text-[15px] font-semibold text-slate-950">劉里長登山社</span>
                        <span class="mt-0.5 block text-[11px] font-medium text-emerald-700">JOIN BEFORE HIKE</span>
                    </span>
                </a>

                <a href="{{ url('/') }}" class="rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600">
                    回首頁
                </a>
            </header>

            <div class="px-4 pb-8">
                <div class="mb-4 rounded-3xl bg-slate-950 p-5 text-white">
                    <p class="text-xs font-semibold text-emerald-200">會員制揪團</p>
                    <h1 class="mt-2 text-2xl font-semibold leading-tight">先加入社群，再安心同行</h1>
                    <div class="mt-4 grid grid-cols-3 gap-2 text-center text-xs">
                        <div class="rounded-2xl bg-white/10 px-2 py-3">
                            <div class="font-semibold text-white">1</div>
                            <div class="mt-1 text-slate-300">建立會員</div>
                        </div>
                        <div class="rounded-2xl bg-white/10 px-2 py-3">
                            <div class="font-semibold text-white">2</div>
                            <div class="mt-1 text-slate-300">看團資訊</div>
                        </div>
                        <div class="rounded-2xl bg-white/10 px-2 py-3">
                            <div class="font-semibold text-white">3</div>
                            <div class="mt-1 text-slate-300">報名同行</div>
                        </div>
                    </div>
                </div>

                <div class="w-full rounded-3xl border border-slate-200 bg-white px-5 py-5 shadow-sm shadow-slate-200/70">
                {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
