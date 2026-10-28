<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        foreach (['name', 'email', 'phone'] as $field) {
            if (! is_string($this->input($field))) {
                continue;
            }
            $value = trim($this->input($field));
            if ($field === 'email') {
                $value = mb_strtolower($value);
            } elseif ($field === 'phone') {
                $value = preg_replace('/[\s()-]+/', '', $value);
                $value = preg_replace('/^\+8860?/', '0', $value);
            }
            $this->merge([$field => $value]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^09[0-9]{8}$/', 'unique:users,phone'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => '請填寫你的稱呼。',
            'name.string' => '請填寫有效的稱呼。',
            'name.max' => '稱呼請勿超過 255 個字。',
            'phone.required' => '請填寫手機號碼，方便主揪確認行程。',
            'phone.string' => '請填寫有效的手機號碼。',
            'phone.regex' => '請填寫 09 開頭的 10 碼手機號碼，也可使用 +886 格式。',
            'phone.unique' => '這個手機號碼已註冊，請登入或使用忘記密碼。',
            'email.required' => '請填寫 Email，作為登入帳號。',
            'email.email' => '請確認 Email 格式，例如 name@example.com。',
            'email.string' => '請填寫有效的 Email。',
            'email.max' => 'Email 請勿超過 255 個字。',
            'email.unique' => '這個 Email 已註冊，請登入或使用忘記密碼。',
            'password.required' => '請設定登入密碼。',
            'password.string' => '請填寫有效的密碼。',
            'password.min' => '密碼至少需要 8 個字元。',
            'password.confirmed' => '兩次輸入的密碼不同，請再確認。',
            'avatar.image' => '頭像必須是圖片。',
            'avatar.mimes' => '頭像請使用 JPG、PNG 或 WebP 格式。',
            'avatar.max' => '頭像大小請勿超過 4 MB。',
        ];
    }
}
