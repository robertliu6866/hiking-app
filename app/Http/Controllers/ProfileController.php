<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use App\Models\User;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
   public function edit(Request $request): View
{
    return view('profile.edit', [
        'user' => $request->user(),
    ]);
}

    /**
     * Update the user's profile information.
     */
public function update(Request $request): RedirectResponse
{
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => [
            'required',
            'string',
            'lowercase',
            'email',
            'max:255',
            Rule::unique(User::class)->ignore($request->user()->id),
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
    ]);

    $user = $request->user();

    $user->fill($validated);

    if ($request->hasFile('avatar')) {
        $avatarPath = $request->file('avatar')->store('avatars', 'public');
        $user->avatar = $avatarPath;
    }

    if ($user->isDirty('email')) {
        $user->email_verified_at = null;
    }

    if (
        filled($user->age) &&
        filled($user->gender) &&
        filled($user->hiking_experience) &&
        filled($user->address) &&
        filled($user->blood_type) &&
        filled($user->emergency_contact_name) &&
        filled($user->emergency_contact_phone) &&
        filled($user->bio)
    ) {
        $user->profile_completed_at = now();
    } else {
        $user->profile_completed_at = null;
    }

    $user->save();

    return redirect()->route('profile.show')->with('status', 'profile-updated');
}
    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
    public function show()
{
    $user = auth()->user();

    return view('profile.show', compact('user'));
}

}
