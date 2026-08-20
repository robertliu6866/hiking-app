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

            <x-guest-welcome-dialog />

        </div>
    </div>

    @livewireScripts
</body>
</html>
