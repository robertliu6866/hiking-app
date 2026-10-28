<x-guest-layout>
    <div class="mb-5">
        <p class="text-sm font-semibold text-emerald-700">加入會員</p>
        <h2 class="mt-1 text-xl font-semibold text-slate-950">30 秒完成，接著挑一團出發</h2>
        <p class="mt-2 text-sm leading-6 text-slate-500">
            會員可以看完整活動、報名同行、在週末許願板 +1，主揪也能用手機聯絡確認。
        </p>
    </div>

    @if ($errors->any())
        <div role="alert" class="mb-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <p class="font-semibold">還差一點，請確認下方標示的資料</p>
            @if ($errors->has('email') || $errors->has('phone'))
                <p class="mt-2">已經註冊過？可以 <a class="underline" href="{{ route('login') }}">登入</a>，或 <a class="underline" href="{{ route('password.request') }}">重設密碼</a>。</p>
            @endif
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-4"
          x-data="{ showPassword: false, submitting: false }"
          @submit="submitting = true" @pageshow.window="submitting = false">
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
                    maxlength="255"
                    placeholder="例：Robert"
                />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="phone" value="手機號碼" />
                <x-text-input
                    id="phone"
                    class="mt-1 block w-full"
                    type="tel"
                    name="phone"
                    :value="old('phone')"
                    required
                    autocomplete="tel"
                    inputmode="tel"
                    maxlength="20"
                    aria-describedby="phone-help"
                    placeholder="例：0912 345 678"
                />
                <p id="phone-help" class="mt-1 text-xs leading-5 text-slate-500">填寫台灣手機號碼，可使用 +886 格式，方便主揪聯絡確認。</p>
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
                    maxlength="255"
                    autocapitalize="none"
                    spellcheck="false"
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
                    x-bind:type="showPassword ? 'text' : 'password'"
                    x-ref="password"
                    @input="$refs.confirmation.setCustomValidity($refs.confirmation.value && $refs.confirmation.value !== $event.target.value ? '兩次輸入的密碼不同' : '')"
                    name="password"
                    required
                    autocomplete="new-password"
                    minlength="8"
                    aria-describedby="password-help"
                    placeholder="至少 8 碼"
                />
                <p id="password-help" class="mt-1 text-xs text-slate-500">至少 8 個字元，請勿與其他網站共用密碼。</p>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password_confirmation" value="再輸入一次密碼" />
                <x-text-input
                    id="password_confirmation"
                    class="mt-1 block w-full"
                    type="password"
                    x-bind:type="showPassword ? 'text' : 'password'"
                    x-ref="confirmation"
                    @input="$event.target.setCustomValidity($event.target.value !== $refs.password.value ? '兩次輸入的密碼不同' : '')"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    minlength="8"
                />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <label class="inline-flex items-center gap-2 text-sm text-slate-600" x-cloak>
            <input type="checkbox" x-model="showPassword" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
            顯示密碼
        </label>

        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3">
            <div class="text-sm font-semibold text-emerald-900">加入後回首頁開始</div>
            <p class="mt-1 text-xs leading-5 text-emerald-700">有團就跟團；沒團就許願 +1，讓社群知道你想去哪。</p>
        </div>

        <button type="submit" class="ui-btn-primary w-full" :disabled="submitting" :aria-busy="submitting">
            <span x-text="submitting ? '正在建立會員…' : '加入會員，開始跟團或許願'">加入會員，開始跟團或許願</span>
        </button>

        <div class="text-center text-sm text-slate-500">
            已經是會員？
            <a class="font-semibold text-emerald-700" href="{{ route('login') }}">
                登入後繼續
            </a>
        </div>

        <p class="text-center text-xs text-slate-500"><a class="underline" href="{{ route('password.request') }}">忘記密碼？找回原本的帳號</a></p>
    </form>
</x-guest-layout>
