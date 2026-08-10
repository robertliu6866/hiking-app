<?php

use App\Http\Controllers\LineBotController;
use Illuminate\Support\Facades\Route;

Route::post('/line/webhook', [LineBotController::class, 'webhook'])
    ->name('line.webhook');
