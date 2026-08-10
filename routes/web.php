<?php

use App\Http\Controllers\Admin\TripController as AdminTripController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LotteryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\TripPaymentController;
use App\Http\Controllers\TripWishController;
use App\Http\Controllers\UserFollowController;
use Illuminate\Support\Facades\Route;

Route::get('/', function (\Illuminate\Http\Request $request) {
    if (! $request->user()) {
        return view('welcome-simple');
    }

    return redirect()->route('trips.index');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/trips', [TripController::class, 'index'])->name('trips.index');

    Route::get('/lotteries/yushan', [LotteryController::class, 'yushan'])->name('lotteries.yushan');

    Route::middleware(\App\Http\Middleware\AdminMiddleware::class)->group(function () {
        Route::get('/trips/create', [TripController::class, 'create'])->name('trips.create');
        Route::post('/trips', [TripController::class, 'store'])->name('trips.store');
        Route::get('/admin', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/admin/trips', [AdminTripController::class, 'index'])->name('admin.trips.index');
    });

    Route::get('/trips/{trip}', [TripController::class, 'show'])->name('trips.show');
    Route::post('/trips/{trip}/join', [TripController::class, 'join'])->name('trips.join');

    Route::get('/payments/{order}', [TripPaymentController::class, 'show'])->name('payments.show');
    Route::post('/payments/{order}/line-pay', [TripPaymentController::class, 'linePay'])->name('payments.line-pay');
    Route::post('/payments/{order}/bank-transfer', [TripPaymentController::class, 'bankTransfer'])->name('payments.bank-transfer');
    Route::post('/payments/{order}/bank-transfer/confirm', [TripPaymentController::class, 'confirmBankTransfer'])->name('payments.bank-transfer.confirm');
    Route::get('/payments/{order}/line-pay/confirm', [TripPaymentController::class, 'linePayConfirm'])->name('payments.line-pay.confirm');
    Route::get('/payments/{order}/line-pay/cancel', [TripPaymentController::class, 'linePayCancel'])->name('payments.line-pay.cancel');

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
