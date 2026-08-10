<x-guest-layout>
    <div class="mb-5">
        <p class="text-sm font-semibold text-emerald-700">加入會員</p>
        <h2 class="mt-1 text-xl font-semibold text-slate-950">30 秒完成，接著挑一團出發</h2>
        <p class="mt-2 text-sm leading-6 text-slate-500">
            會員可以看完整活動、報名同行、在週末許願板 +1，主揪也能用手機聯絡確認。
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div class="grid grid-cols-1 gap-4">
            <div>
                <x-input-label for="name" value="怎麼稱呼你" />
                <x-text-input
                    id="name"
                    class="mt-1 block w-full"
                    type="text"
                    name="name"
                    :value="old('name')"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="例：Robert"
                />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="phone" value="手機號碼" />
                <x-text-input
                    id="phone"
                    class="mt-1 block w-full"
                    type="text"
                    name="phone"
                    :value="old('phone')"
                    required
                    autocomplete="tel"
                    placeholder="主揪確認行程時使用"
                />
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="email" value="Email" />
                <x-text-input
                    id="email"
                    class="mt-1 block w-full"
                    type="email"
                    name="email"
                    :value="old('email')"
                    required
                    autocomplete="username"
                    placeholder="登入帳號"
                />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password" value="設定密碼" />
                <x-text-input
                    id="password"
                    class="mt-1 block w-full"
                    type="password"
                    name="password"
                    required
                    autocomplete="new-password"
                    placeholder="至少 8 碼"
                />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password_confirmation" value="再輸入一次密碼" />
                <x-text-input
                    id="password_confirmation"
                    class="mt-1 block w-full"
                    type="password"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3">
            <div class="text-sm font-semibold text-emerald-900">加入後回首頁開始</div>
            <p class="mt-1 text-xs leading-5 text-emerald-700">有團就跟團；沒團就許願 +1，讓社群知道你想去哪。</p>
        </div>

        <button type="submit" class="ui-btn-primary w-full">
            加入會員，開始跟團或許願
        </button>

        <div class="text-center text-sm text-slate-500">
            已經是會員？
            <a class="font-semibold text-emerald-700" href="{{ route('login') }}">
                登入後繼續
            </a>
        </div>
    </form>
</x-guest-layout>
