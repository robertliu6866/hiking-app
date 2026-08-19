<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LotteryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TripWishController;
use App\Http\Controllers\UserFollowController;
use App\Models\TripWish;
use Illuminate\Support\Facades\Route;

Route::get('/', function (\Illuminate\Http\Request $request) {
    if (! $request->user()) {
        $wishes = TripWish::query()
            ->with(['user', 'users'])
            ->withCount('users')
            ->where(function ($query) {
                $query->whereNull('wished_date')
                    ->orWhereDate('wished_date', '>=', today());
            })
            ->orderByRaw('wished_date is null')
            ->orderBy('wished_date')
            ->latest()
            ->take(4)
            ->get();

        return view('welcome-simple', compact('wishes'));
    }

    return redirect()->route('lotteries.yushan');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::post('/onboarding/complete', [OnboardingController::class, 'complete'])->name('onboarding.complete');

    Route::get('/lotteries/yushan', [LotteryController::class, 'yushan'])->name('lotteries.yushan');

    Route::post('/trip-wishes', [TripWishController::class, 'store'])->name('trip-wishes.store');
    Route::post('/trip-wishes/{tripWish}/join', [TripWishController::class, 'join'])->name('trip-wishes.join');

    Route::post('/users/{user}/follow', [UserFollowController::class, 'follow'])->name('users.follow');
    Route::delete('/users/{user}/follow', [UserFollowController::class, 'unfollow'])->name('users.unfollow');

    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/show', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/member-center', function () {
        return view('profile.member-center');
    })->name('member-center');
});

require __DIR__.'/auth.php';
