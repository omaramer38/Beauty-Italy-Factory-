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
    Route::get('/items', [PhaseOneApiController::class, 'getItems']);
    Route::get('/warehouse/stats', [PhaseOneApiController::class, 'getWarehouseStats']);
    Route::get('/lots', [PhaseOneApiController::class, 'getLots']);
    Route::post('/lots/receipt', [PhaseOneApiController::class, 'createGoodsReceipt']);
    Route::put('/lots/{id}/location', [PhaseOneApiController::class, 'updateLotLocation']);
    Route::get('/lots/{lot}/ledger', [PhaseOneApiController::class, 'getLotLedger']);
    Route::get('/formulations/{code?}', [PhaseOneApiController::class, 'getFormulation']);
    Route::get('/dispensing/{batch?}', [PhaseOneApiController::class, 'getDispensing']);
    Route::post('/dispensing/sign', [PhaseOneApiController::class, 'signDispenseStep']);
    Route::get('/quality/{sample?}', [PhaseOneApiController::class, 'getQualityReview']);
    Route::post('/quality/release', [PhaseOneApiController::class, 'releaseBatch']);
    Route::get('/traceability/{lot?}', [PhaseOneApiController::class, 'getTraceability']);
});
