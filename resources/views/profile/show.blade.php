<x-app-layout>
    <div class="min-h-screen bg-gray-100 flex justify-center">
        <div class="max-w-md w-full bg-white min-h-screen">

            <!-- 頭像區 -->
            <div class="flex flex-col items-center text-center py-8 border-b space-y-2">
                @if($user->avatar)
                    <img src="{{ asset('storage/' . $user->avatar) }}"
                         class="w-20 h-20 rounded-full object-cover">
                @else
                    <div class="w-20 h-20 rounded-full bg-gray-200 flex items-center justify-center text-lg font-semibold">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif

                <div class="font-semibold text-gray-900">
                    {{ $user->name }}
                </div>

                <div class="text-sm text-gray-500">
                    {{ $user->email }}
                </div>
                @if($user->profile_completed_at)
    <div class="text-xs text-green-600 bg-green-50 px-2 py-1 rounded-full">
        ✔ 資料已完成
    </div>
@else
    <div class="text-xs text-orange-500 bg-orange-50 px-2 py-1 rounded-full">
        ⚠ 尚未完成
    </div>
@endif

                <a href="{{ route('profile.edit') }}"
                   class="text-sm text-gray-600 underline pt-1">
                    編輯資料
                </a>

            </div>

            <!-- 區塊 -->
            <div class="divide-y">

                <!-- 基本資料 -->
                <div class="py-5 px-5 space-y-3">
                    <div class="text-xs text-gray-400">基本資料</div>

                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">電話</span>
                        <span>{{ $user->phone ?? '-' }}</span>
                    </div>

                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">年齡</span>
                        <span>{{ $user->age ?? '-' }}</span>
                    </div>

                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">性別</span>
                        <span>{{ $user->gender ?? '-' }}</span>
                    </div>

                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">登山年資</span>
                        <span>{{ $user->hiking_experience ?? '-' }}</span>
                    </div>
                </div>

                <!-- 其他資料 -->
                <div class="py-5 px-5 space-y-3">
                    <div class="text-xs text-gray-400">其他資料</div>

                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">血型</span>
                        <span>{{ $user->blood_type ?? '-' }}</span>
                    </div>

                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">緊急聯絡人</span>
                        <span>{{ $user->emergency_contact_name ?? '-' }}</span>
                    </div>

                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">緊急電話</span>
                        <span>{{ $user->emergency_contact_phone ?? '-' }}</span>
                    </div>
                </div>

                <!-- 自我介紹 -->
                <div class="py-5 px-5">
                    <div class="text-xs text-gray-400 mb-2">自我介紹</div>
                    <div class="text-sm text-gray-800 leading-relaxed">
                        {{ $user->bio ?? '尚未填寫' }}
                    </div>
                </div>

                <!-- 會員資訊 -->
                <div class="py-5 px-5 space-y-3">
                    <div class="text-xs text-gray-400">會員資訊</div>

                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">會員狀態</span>
                        <span>{{ $user->membership_status ?? '一般會員' }}</span>
                    </div>

                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">免費次數</span>
                        <span>{{ $user->free_trial_quota ?? 0 }}</span>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
