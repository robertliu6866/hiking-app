<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class MemberCenter extends Component
{
    use WithFileUploads;

    public User $user;

    public bool $editing = false;

    public string $name = '';
    public string $email = '';
    public ?string $phone = null;
    public ?int $age = null;
    public ?string $gender = null;
    public ?string $hiking_experience = null;
    public ?string $address = null;
    public ?string $blood_type = null;
    public ?string $emergency_contact_name = null;
    public ?string $emergency_contact_phone = null;
    public ?string $bio = null;

    public $avatar;

    public function mount(): void
    {
        $this->user = auth()->user();
        $this->fillForm();
    }

    private function fillForm(): void
    {
        $this->name = $this->user->name ?? '';
        $this->email = $this->user->email ?? '';
        $this->phone = $this->user->phone;
        $this->age = $this->user->age;
        $this->gender = $this->user->gender;
        $this->hiking_experience = $this->user->hiking_experience;
        $this->address = $this->user->address;
        $this->blood_type = $this->user->blood_type;
        $this->emergency_contact_name = $this->user->emergency_contact_name;
        $this->emergency_contact_phone = $this->user->emergency_contact_phone;
        $this->bio = $this->user->bio;
    }

    public function toggleEdit(): void
    {
        if ($this->editing) {
            $this->fillForm();
        }
        $this->editing = ! $this->editing;
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
            'address' => ['nullable', 'string', 'max:255'],
            'blood_type' => ['nullable', 'string', 'max:10'],
            'emergency_contact_name' => ['nullable', 'string', 'max:100'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
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
            'address' => $validated['address'] ?? null,
            'blood_type' => $validated['blood_type'] ?? null,
            'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
            'bio' => $validated['bio'] ?? null,
        ]);

        if ($this->user->isDirty('email')) {
            $this->user->email_verified_at = null;
        }

        if ($this->avatar) {
            if ($this->user->avatar && Storage::disk('public')->exists($this->user->avatar)) {
                Storage::disk('public')->delete($this->user->avatar);
            }
            $this->user->avatar = $this->avatar->store('avatars', 'public');
        }

        if (
            filled($this->user->age) &&
            filled($this->user->gender) &&
            filled($this->user->hiking_experience) &&
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
        $this->user = $this->user->fresh();
        $this->reset('avatar');
        $this->editing = false;

        session()->flash('success', '資料已更新');
    }

    public function render()
    {
        return view('livewire.member-center');
    }
}
