<?php

use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiGovernanceController;
use App\Http\Controllers\Api\ApiInventoryController;
use App\Http\Controllers\Api\ApiMasterDataController;
use Illuminate\Support\Facades\Route;

// Public Mobile Auth Routes
Route::prefix('auth')->group(function () {
    Route::post('login', [ApiAuthController::class, 'login']);
});

// Protected Mobile API Routes (Requires JWT Bearer Token)
Route::middleware('auth:api')->group(function () {
    // User Session
    Route::prefix('auth')->group(function () {
        Route::get('me', [ApiAuthController::class, 'me']);
        Route::post('logout', [ApiAuthController::class, 'logout']);
    });

    // Master Data Lookups for Mobile Dropdowns
    Route::get('master-data/all', [ApiMasterDataController::class, 'all']);

    // Mobile Dashboard KPI Stats
    Route::get('inventory/stats', [ApiInventoryController::class, 'stats']);

    // Inventory Items CRUD
    Route::prefix('inventory')->group(function () {
        Route::get('items', [ApiInventoryController::class, 'index']);
        Route::post('items', [ApiInventoryController::class, 'store']);
        Route::get('items/{id}', [ApiInventoryController::class, 'show']);
        Route::post('items/{id}', [ApiInventoryController::class, 'update']); // POST for multipart/form-data support
        Route::put('items/{id}', [ApiInventoryController::class, 'update']);
        Route::delete('items/{id}', [ApiInventoryController::class, 'destroy']);
    });

    // Governance: Pact & Finalization
    Route::prefix('governance')->group(function () {
        Route::post('sign-pact', [ApiGovernanceController::class, 'signPact']);
        Route::post('finalize', [ApiGovernanceController::class, 'finalize']);
    });
});
