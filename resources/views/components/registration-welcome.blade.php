@if (session('status') === 'member-created')
    <section role="status" class="ui-card mx-4 mt-5 border-emerald-200 bg-emerald-50">
        <h2 class="text-base font-semibold text-emerald-950">{{ auth()->user()->name }}，歡迎加入！</h2>
        <p class="mt-2 text-sm leading-6 text-emerald-800">帳號已建立，可以開始找團或許願。出發前也可以補上登山經驗、個人偏好與緊急聯絡人，讓山友更了解你。</p>
        <a href="{{ route('member-center') }}" class="ui-btn-soft mt-3">完善會員資料</a>
    </section>
@endif
