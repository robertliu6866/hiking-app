<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

  

    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="margin:0; background:#e5e7eb;">
    <div style="min-height:100vh; display:flex; justify-content:center;">
        <div
            style="
                width:100%;
                max-width:430px;
                min-height:100vh;
                background:#f3f4f6;
                border-left:1px solid #d1d5db;
                border-right:1px solid #d1d5db;
            "
        >
            @include('layouts.navigation')

            @isset($header)
                <header style="background:white; border-bottom:1px solid #e5e7eb;">
                    <div style="padding:16px;">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main>
                {{ $slot }}
            </main>

            @if (auth()->user()?->onboarding_seen_at === null)
                <div
                    x-data="{
                        open: true,
                        saving: false,
                        async complete() {
                            if (this.saving) return;

                            this.saving = true;

                            try {
                                const response = await fetch(@js(route('onboarding.complete')), {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                        'Accept': 'application/json',
                                    },
                                });

                                if (! response.ok) throw new Error('Unable to save onboarding status.');

                                this.open = false;
                            } finally {
                                this.saving = false;
                            }
                        }
                    }"
                    x-show="open"
                    x-transition.opacity
                    x-cloak
                    class="fixed inset-0 z-[60] flex items-end bg-slate-950/55 px-4 py-4 sm:items-center sm:justify-center"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="how-we-play-title"
                >
                    <section x-show="open" x-transition.scale.origin.bottom class="w-full max-w-[390px] overflow-hidden rounded-[2rem] bg-white shadow-2xl">
                        <div class="bg-[linear-gradient(135deg,#064e3b_0%,#059669_100%)] px-6 pb-7 pt-6 text-white">
                            <span class="text-2xl" aria-hidden="true">⛰️</span>
                            <h2 id="how-we-play-title" class="mt-2 text-2xl font-semibold tracking-tight">我們怎麼玩？</h2>
                            <p class="mt-2 text-sm leading-6 text-emerald-50">一起爬山、一起共乘、互相 Cover。</p>
                        </div>

                        <div class="space-y-5 px-6 py-6">
                            <div class="flex gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-lg">👤</span>
                                <div>
                                    <h3 class="text-sm font-semibold text-slate-950">真人加入</h3>
                                    <p class="mt-1 text-sm leading-6 text-slate-500">知道跟誰一起上山，安心一點。</p>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-lg">⚖️</span>
                                <div>
                                    <h3 class="text-sm font-semibold text-slate-950">大家輪流</h3>
                                    <p class="mt-1 text-sm leading-6 text-slate-500">不讓少數人一直付出。這次你揪，下次我來。</p>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-lg">📍</span>
                                <div>
                                    <h3 class="text-sm font-semibold text-slate-950">紀錄都在這裡</h3>
                                    <p class="mt-1 text-sm leading-6 text-slate-500">參加過什麼、揪過幾次，平台幫你記著。不用翻群組、不靠印象。</p>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium leading-6 text-emerald-800">
                                有來有往，才能一起玩得久。
                            </div>

                            <button type="button" class="ui-btn-primary w-full" x-on:click="complete()" x-bind:disabled="saving">
                                <span x-show="! saving">認同，山上見 ⛰️</span>
                                <span x-cloak x-show="saving">處理中…</span>
                            </button>
                        </div>
                    </section>
                </div>
            @endif
        </div>
    </div>

    @livewireScripts
</body>
</html>
