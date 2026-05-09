<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Dashboard\ServiceController;


Route::middleware(['auth', 'manager'])
    ->get('/dashboard', function() {
        $user = auth()->user();

        return Inertia::render('Dashboard/Index', [
            'business' => $user->business,
            'bookingLink' => url('/book/' . $user->business->slug),
        ]);
    })
    ->name('dashboard');

Route::middleware(['auth', 'manager'])
    ->prefix('dashboard')
    ->name('dashboard.')
    ->group(function () {
        Route::resource('services', ServiceController::class);
    });