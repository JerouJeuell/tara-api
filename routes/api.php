<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PartnershipController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\ChecklistController;
use Illuminate\Support\Facades\Route;

// ── Public Routes ──
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// ── Protected Routes ──
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me',      [AuthController::class, 'me']);
    });

    // Partnerships
    Route::prefix('partnerships')->group(function () {
        Route::get('/',         [PartnershipController::class, 'me']);
        Route::post('/invite',  [PartnershipController::class, 'invite']);
        Route::post('/accept',  [PartnershipController::class, 'accept']);
        Route::delete('/leave', [PartnershipController::class, 'leave']);
        Route::get('/pending',  [PartnershipController::class, 'pending']);
    });

    // Events
    Route::apiResource('events', EventController::class);

    // Checklists
    Route::apiResource('checklists', ChecklistController::class)->only([
        'index', 'store', 'show', 'destroy'
    ]);
    Route::post('checklists/{id}/items',                    [ChecklistController::class, 'addItem']);
    Route::patch('checklists/{id}/items/{itemId}/toggle',   [ChecklistController::class, 'toggleItem']);
    Route::delete('checklists/{id}/items/{itemId}',         [ChecklistController::class, 'deleteItem']);

});