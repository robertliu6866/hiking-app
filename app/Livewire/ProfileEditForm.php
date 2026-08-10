<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProfileEditForm extends Component
{
    use WithFileUploads;

    public User $user;

    public string $name = '';
    public string $email = '';
    public ?string $phone = null;
    public ?int $age = null;
    public ?string $gender = null;
    public ?string $hiking_experience = null;
    public array $preferred_regions = [];
    public array $available_days = [];
    public array $transport_modes = [];
    public array $preferred_route_modes = [];
    public array $hiking_styles = [];
    public ?int $preferred_difficulty_min = null;
    public ?int $preferred_difficulty_max = null;
    public ?string $address = null;
    public ?string $blood_type = null;
    public ?string $emergency_contact_name = null;
    public ?string $emergency_contact_phone = null;
    public ?string $bio = null;

    public $avatar;

    public function mount(User $user): void
    {
        $this->user = $user;

        $this->name = $user->name ?? '';
        $this->email = $user->email ?? '';
        $this->phone = $user->phone;
        $this->age = $user->age;
        $this->gender = $user->gender;
        $this->hiking_experience = $user->hiking_experience;
        $this->preferred_regions = $user->preferred_regions ?? [];
        $this->available_days = $user->available_days ?? [];
        $this->transport_modes = $user->transport_modes ?? [];
        $this->preferred_route_modes = $user->preferred_route_modes ?? [];
        $this->hiking_styles = $user->hiking_styles ?? [];
        $this->preferred_difficulty_min = $user->preferred_difficulty_min;
        $this->preferred_difficulty_max = $user->preferred_difficulty_max;
        $this->address = $user->address;
        $this->blood_type = $user->blood_type;
        $this->emergency_contact_name = $user->emergency_contact_name;
        $this->emergency_contact_phone = $user->emergency_contact_phone;
        $this->bio = $user->bio;
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user->id),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'age' => ['nullable', 'integer', 'min:1', 'max:120'],
            'gender' => ['nullable', 'string', 'max:20'],
            'hiking_experience' => ['nullable', 'string', 'max:50'],
            'preferred_regions' => ['array'],
            'preferred_regions.*' => ['string', 'in:北部,中部,南部,東部,離島'],
            'available_days' => ['array'],
            'available_days.*' => ['string', 'in:weekday,weekend,holiday'],
            'transport_modes' => ['array'],
            'transport_modes.*' => ['string', 'in:drive,carpool,public_transport'],
            'preferred_route_modes' => ['array'],
            'preferred_route_modes.*' => ['string', 'in:single,traverse,custom'],
            'hiking_styles' => ['array'],
            'hiking_styles.*' => ['string', 'in:slow,photo,training,newbie_friendly,challenge'],
            'preferred_difficulty_min' => ['nullable', 'integer', 'min:1', 'max:5'],
            'preferred_difficulty_max' => ['nullable', 'integer', 'min:1', 'max:5', 'gte:preferred_difficulty_min'],
            'address' => ['nullable', 'string', 'max:255'],
            'blood_type' => ['nullable', 'string', 'max:10'],
            'emergency_contact_name' => ['nullable', 'string', 'max:100'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    public function togglePreference(string $field, string $value): void
    {
        if (! in_array($field, [
            'preferred_regions',
            'available_days',
            'transport_modes',
            'preferred_route_modes',
            'hiking_styles',
        ], true)) {
            return;
        }

        $values = $this->{$field};

        if (in_array($value, $values, true)) {
            $this->{$field} = array_values(array_filter($values, fn (string $item) => $item !== $value));

            return;
        }

        $values[] = $value;
        $this->{$field} = array_values(array_unique($values));
    }

    public function save(): void
    {
        $validated = $this->validate();

        $this->user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'age' => $validated['age'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'hiking_experience' => $validated['hiking_experience'] ?? null,
            'preferred_regions' => array_values($validated['preferred_regions'] ?? []),
            'available_days' => array_values($validated['available_days'] ?? []),
            'transport_modes' => array_values($validated['transport_modes'] ?? []),
            'preferred_route_modes' => array_values($validated['preferred_route_modes'] ?? []),
            'hiking_styles' => array_values($validated['hiking_styles'] ?? []),
            'preferred_difficulty_min' => $validated['preferred_difficulty_min'] ?? null,
            'preferred_difficulty_max' => $validated['preferred_difficulty_max'] ?? null,
            'address' => $validated['address'] ?? null,
            'blood_type' => $validated['blood_type'] ?? null,
            'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
            'bio' => $validated['bio'] ?? null,
        ]);

        // email 變更 → 重設驗證
        if ($this->user->isDirty('email')) {
            $this->user->email_verified_at = null;
        }

        // 處理頭像
        if ($this->avatar) {
            if ($this->user->avatar && Storage::disk('public')->exists($this->user->avatar)) {
                Storage::disk('public')->delete($this->user->avatar);
            }

            $this->user->avatar = $this->avatar->store('avatars', 'public');
        }

        // 判斷資料是否完整
        if (
            filled($this->user->age) &&
            filled($this->user->gender) &&
            filled($this->user->hiking_experience) &&
            filled($this->user->preferred_regions) &&
            filled($this->user->available_days) &&
            filled($this->user->transport_modes) &&
            filled($this->user->preferred_route_modes) &&
            filled($this->user->hiking_styles) &&
            filled($this->user->preferred_difficulty_min) &&
            filled($this->user->preferred_difficulty_max) &&
            filled($this->user->address) &&
            filled($this->user->blood_type) &&
            filled($this->user->emergency_contact_name) &&
            filled($this->user->emergency_contact_phone) &&
            filled($this->user->bio)
        ) {
            $this->user->profile_completed_at = now();
        } else {
            $this->user->profile_completed_at = null;
        }

        $this->user->save();

        // 重新載入最新資料
        $this->user = $this->user->fresh();

        // 清空上傳
        $this->reset('avatar');

        session()->flash('success', '資料已更新');

        $this->dispatch('profile-saved');
    }

    public function render()
    {
        return view('livewire.profile-edit-form');
    }
}
