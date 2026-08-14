<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-5">
        <p class="text-sm font-semibold text-emerald-700">會員登入</p>
        <h2 class="mt-1 text-xl font-semibold text-slate-950">回到你的登山社首頁</h2>
        <p class="mt-2 text-sm leading-6 text-slate-500">
            登入後可以直接跟團、取消報名，或在週末許願板 +1。
        </p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input
                id="email"
                class="mt-1 block w-full"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username"
                placeholder="你的登入帳號"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="密碼" />
            <x-text-input
                id="password"
                class="mt-1 block w-full"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="輸入密碼"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-emerald-600 shadow-sm focus:ring-emerald-500" name="remember">
                <span class="ms-2 text-sm text-slate-600">記住我</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-medium text-slate-500" href="{{ route('password.request') }}">
                    忘記密碼
                </a>
            @endif
        </div>

        <button type="submit" class="ui-btn-primary w-full">
            登入，繼續跟團或許願
        </button>

        <div class="text-center text-sm text-slate-500">
            還不是會員？
            <a class="font-semibold text-emerald-700" href="{{ route('register') }}">
                先加入會員
            </a>
        </div>
    </form>

    <div class="my-5 flex items-center gap-3 text-xs text-slate-400">
        <div class="h-px flex-1 bg-slate-200"></div>
        或
        <div class="h-px flex-1 bg-slate-200"></div>
    </div>

    @error('line')
        <p class="mb-3 text-sm text-red-600">{{ $message }}</p>
    @enderror

    <a href="{{ route('login.line') }}" class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#06C755] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#05b84e]">
        <span class="flex h-5 w-5 items-center justify-center rounded bg-white text-xs font-bold text-[#06C755]">LINE</span>
        使用 LINE 登入
    </a>
</x-guest-layout>
