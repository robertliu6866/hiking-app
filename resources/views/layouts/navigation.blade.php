<nav class="border-b border-emerald-100 bg-white">
    <div class="mx-auto flex h-16 w-full max-w-[430px] items-center justify-between px-4">
        <a href="{{ route('lotteries.yushan') }}" class="font-semibold text-emerald-700">
            劉里長登山社
        </a>

        <div class="flex items-center gap-3 text-sm">
            <a href="{{ route('lotteries.yushan') }}" class="font-medium text-slate-700">
                許願
            </a>
            <a href="{{ route('dashboard') }}" class="font-medium text-slate-700">
                我的許願
            </a>
            <a href="{{ route('member-center') }}" class="font-medium text-slate-700">
                會員
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="font-medium text-slate-400">
                    登出
                </button>
            </form>
        </div>
    </div>
</nav>
