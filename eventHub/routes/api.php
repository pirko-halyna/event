<?php

use App\Http\Controllers\{EventRegistrationController,
    EventTicketTypeController,
    OrderController,
    OrganizerController,
    LocationController,
    AuthController,
    CategoryController,
    EventController,
    UserController};
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:login')->name('auth.login');

    Route::post('logout', [AuthController::class, 'logout'])
        ->middleware('auth.token')->name('auth.logout');

    Route::post('register', [AuthController::class, 'register'])
        ->middleware('throttle:register')->name('auth.register');

    Route::post('password-reset/request', [AuthController::class, 'passwordResetRequest'])
        ->middleware('throttle:password-reset')->name('auth.password-reset.request');

    Route::post('password-reset/confirm', [AuthController::class, 'passwordResetConfirm'])
        ->middleware('throttle:password-reset')->name('auth.password-reset.confirm');
});

Route::apiResource('categories', CategoryController::class)->only('index');

Route::apiResource('organizers', OrganizerController::class)
    ->only(['index', 'show']);

Route::apiResource('locations', LocationController::class)->only('index');

Route::apiResource('events', EventController::class)->only(['index', 'show']);

Route::get('events/{event}/ticket-types', [EventTicketTypeController::class, 'index'])
    ->name('events.ticket-types.index');

Route::middleware('auth.token')->group(function () {
    Route::apiResource('events', EventController::class)->only(['store', 'update', 'destroy']);

    Route::apiResource('events.ticket-types', EventTicketTypeController::class)
        ->only(['store', 'update', 'destroy']);

    Route::post('/events/{event}/favourite', [EventController::class, 'addToFavourite'])
        ->name('events.add-favourite');
    Route::delete('/events/{event}/favourite', [EventController::class, 'removeFromFavourite'])
        ->name('events.remove-favourite');
    Route::get('/users/favourite-events', [UserController::class, 'getFavouriteEvents'])
        ->name('users.favourite-events');
    Route::post('orders', [OrderController::class, 'store'])->name('orders.store');
    Route::post('/events/{event}/registrations', [EventRegistrationController::class, 'register'])
        ->name('events.register');
});
