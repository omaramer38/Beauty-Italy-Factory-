<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PhaseOneApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Phase 1 - Controlled Core API Routes
Route::prefix('v1')->group(function () {
    Route::get('/dashboard', [PhaseOneApiController::class, 'getDashboard']);
    Route::get('/lots', [PhaseOneApiController::class, 'getLots']);
    Route::get('/lots/{lot}/ledger', [PhaseOneApiController::class, 'getLotLedger']);
    Route::get('/formulations/{code?}', [PhaseOneApiController::class, 'getFormulation']);
    Route::get('/dispensing/{batch?}', [PhaseOneApiController::class, 'getDispensing']);
    Route::post('/dispensing/sign', [PhaseOneApiController::class, 'signDispenseStep']);
    Route::get('/quality/{sample?}', [PhaseOneApiController::class, 'getQualityReview']);
    Route::post('/quality/release', [PhaseOneApiController::class, 'releaseBatch']);
    Route::get('/traceability/{lot?}', [PhaseOneApiController::class, 'getTraceability']);
});
