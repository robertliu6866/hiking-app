<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Support\AuthRedirect;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): View
    {
        AuthRedirect::remember($request);

        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $avatarPath = null;

        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'avatar' => $avatarPath,
            'free_trial_quota' => 2,
            'membership_status' => 'trial',
        ]);

        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->intended(route('lotteries.yushan', absolute: false))
            ->with('status', 'member-created');
    }
}
