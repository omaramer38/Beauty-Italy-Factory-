<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PhaseOneApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Phase 1 - Controlled Core API Routes
Route::prefix('v1')->group(function () {
    // Operations & Dashboard
    Route::get('/dashboard', [PhaseOneApiController::class, 'getDashboard']);
    Route::get('/search', [PhaseOneApiController::class, 'universalSearch']);
    Route::get('/qr/lookup', [PhaseOneApiController::class, 'qrLookup']);

    // Warehouse & Inventory
    Route::get('/items', [PhaseOneApiController::class, 'getItems']);
    Route::get('/warehouse/stats', [PhaseOneApiController::class, 'getWarehouseStats']);
    Route::get('/lots', [PhaseOneApiController::class, 'getLots']);
    Route::post('/lots/receipt', [PhaseOneApiController::class, 'createGoodsReceipt']);
    Route::put('/lots/{id}/location', [PhaseOneApiController::class, 'updateLotLocation']);
    Route::get('/lots/{lot}/ledger', [PhaseOneApiController::class, 'getLotLedger']);

    // Formulation Studio
    Route::get('/formulations/{code?}', [PhaseOneApiController::class, 'getFormulation']);
    Route::post('/formulations', [PhaseOneApiController::class, 'storeFormulation']);
    Route::post('/formulations/{code}/lines', [PhaseOneApiController::class, 'addFormulationLine']);
    Route::post('/formulations/{code}/create-batch', [PhaseOneApiController::class, 'createBatchFromFormulation']);

    // Dispensing & MES
    Route::get('/dispensing/{batch?}', [PhaseOneApiController::class, 'getDispensing']);
    Route::post('/dispensing/sign', [PhaseOneApiController::class, 'signDispenseStep']);

    // Quality Control (LIMS & QA Release)
    Route::get('/quality/{sample?}', [PhaseOneApiController::class, 'getQualityReview']);
    Route::post('/quality/release', [PhaseOneApiController::class, 'releaseBatch']);
    Route::post('/quality/reject', [PhaseOneApiController::class, 'rejectBatch']);
    Route::post('/quality/test/update', [PhaseOneApiController::class, 'updateTestResult']);

    // Traceability Matrix & Recall Management
    Route::get('/traceability/{lot?}', [PhaseOneApiController::class, 'getTraceability']);
    Route::post('/traceability/recall-hold', [PhaseOneApiController::class, 'triggerRecallHold']);
});
