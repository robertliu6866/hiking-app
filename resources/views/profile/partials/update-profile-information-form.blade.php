<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            會員基本資料
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            更新你的姓名、手機號碼與大頭貼。
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div class="flex flex-col items-center">
            @if ($user->avatar)
                <img
                    src="{{ asset('storage/' . $user->avatar) }}"
                    alt="會員頭像"
                     class="w-20 h-20 mx-auto rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-2xl font-bold border-2 border-indigo-100 shadow-sm"
                >
            @else
                <div class="w-24 h-24 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-3xl font-bold border-4 border-indigo-100 shadow-sm">
                    {{ mb_substr($user->name, 0, 1) }}
                </div>
            @endif

            <div class="mt-4 w-full">
                <label for="avatar" class="block text-sm font-medium text-gray-700">
                    大頭貼
                </label>
                <input
                    id="avatar"
                    name="avatar"
                    type="file"
                    accept="image/*"
                    class="mt-2 block w-full rounded-xl border border-gray-300 px-3 py-2 text-sm"
                >
                @error('avatar')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <x-input-label for="name" value="姓名" />
            <x-text-input
                id="name"
                name="name"
                type="text"
                class="mt-1 block w-full"
                :value="old('name', $user->name)"
                required
                autofocus
                autocomplete="name"
            />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="phone" value="手機號碼" />
            <x-text-input
                id="phone"
                name="phone"
                type="text"
                class="mt-1 block w-full"
                :value="old('phone', $user->phone)"
                autocomplete="tel"
            />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input
                id="email"
                name="email"
                type="email"
                class="mt-1 block w-full"
                :value="old('email', $user->email)"
                autocomplete="username"
            />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>儲存資料</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >已儲存</p>
            @endif
        </div>
    </form>
</section>
