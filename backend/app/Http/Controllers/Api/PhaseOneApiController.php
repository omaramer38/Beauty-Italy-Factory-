<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PhaseOneApiController extends Controller
{
    /**
     * Dashboard Overview Metrics & Line Status
     */
    public function getDashboard()
    {
        $activeBatches = DB::table('production_batches')->whereIn('status', ['Running', 'QC Hold'])->count();
        $quarantineLots = DB::table('lots')->where('status', 'Quarantine')->count();
        
        $expiringCount = DB::table('lots')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', Carbon::now()->addDays(60))
            ->count();

        $pendingQARelease = DB::table('qc_samples')->where('status', 'Pending QA release')->count();

        $lines = DB::table('production_batches')->get()->map(function ($b) {
            $tone = 'approved';
            if ($b->status === 'QC Hold') $tone = 'warning';
            if ($b->status === 'Changeover') $tone = 'info';

            return [
                'line' => $b->line_code,
                'product' => $b->product_name,
                'batch' => $b->batch_number,
                'start' => $b->start_time,
                'end' => $b->end_time,
                'progress' => $b->progress,
                'status' => $b->status,
                'tone' => $tone,
            ];
        });

        $attention = [
            ['title' => 'QC overdue', 'code' => 'RM-RET-24018', 'due' => '2h overdue', 'tone' => 'danger'],
            ['title' => 'Expiry approaching', 'code' => 'LOT-GLY-8821', 'due' => '18 days', 'tone' => 'warning'],
            ['title' => 'Scale calibration', 'code' => 'WS-03', 'due' => 'Due today', 'tone' => 'warning'],
            ['title' => 'QA signature', 'code' => 'B-25-0398', 'due' => 'Waiting 46m', 'tone' => 'info'],
        ];

        return response()->json([
            'metrics' => [
                ['label' => 'Active batches on lines', 'value' => (string)$activeBatches, 'detail' => 'Running on production lines', 'delta' => '2 near completion', 'icon' => 'scale', 'tone' => 'blue'],
                ['label' => 'Lots in quarantine', 'value' => (string)$quarantineLots, 'detail' => 'Needs urgent QC sampling', 'delta' => '6 due today', 'icon' => 'clock', 'tone' => 'amber'],
                ['label' => 'Expiring lots · FEFO alerts', 'value' => (string)$expiringCount, 'detail' => 'Expire within 60 days', 'delta' => 'Review FEFO', 'icon' => 'alert', 'tone' => 'rose'],
                ['label' => 'Pending QA release approvals', 'value' => (string)$pendingQARelease, 'detail' => '€142k controlled inventory', 'delta' => '2 overdue', 'icon' => 'shield', 'tone' => 'green'],
            ],
            'lines' => $lines,
            'attention' => $attention,
        ]);
    }

    /**
     * Inventory Lots with FEFO calculation
     */
    public function getLots(Request $request)
    {
        $query = DB::table('lots')
            ->join('items', 'lots.item_id', '=', 'items.id')
            ->select(
                'lots.id',
                'lots.lot_number as lot',
                'items.code',
                'items.name',
                'lots.status',
                'lots.quantity as qty',
                'lots.unit',
                'lots.storage_location as location',
                'lots.expiry_date',
                'lots.supplier_name',
                'lots.source_document'
            );

        if ($request->has('status') && $request->status !== 'All') {
            $query->where('lots.status', $request->status);
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('lots.lot_number', 'like', "%{$search}%")
                  ->orWhere('items.name', 'like', "%{$search}%")
                  ->orWhere('items.code', 'like', "%{$search}%");
            });
        }

        $lots = $query->orderBy('lots.id', 'asc')->get()->map(function ($lot) {
            $fefo = '';
            $expiryFormatted = '—';
            if ($lot->expiry_date) {
                $expiry = Carbon::parse($lot->expiry_date);
                $expiryFormatted = $expiry->format('d M Y');
                $diffDays = Carbon::now()->diffInDays($expiry, false);
                if ($diffDays > 0 && $diffDays <= 90) {
                    $fefo = "{$diffDays} days";
                }
            }

            return [
                'id' => $lot->id,
                'code' => $lot->code,
                'name' => $lot->name,
                'lot' => $lot->lot,
                'expiry' => $expiryFormatted,
                'location' => $lot->location,
                'status' => $lot->status,
                'qty' => number_format($lot->qty, 2),
                'unit' => $lot->unit,
                'fefo' => $fefo,
                'supplier' => $lot->supplier_name,
                'sourceDocument' => $lot->source_document,
            ];
        });

        return response()->json([
            'lots' => $lots,
            'summary' => [
                'totalSkus' => '1,284',
                'inventoryValue' => '€2.84M',
                'quarantined' => DB::table('lots')->where('status', 'Quarantine')->count() . ' lots',
                'expiring' => '3 lots',
            ]
        ]);
    }

    /**
     * Get Immutable Ledger for a Specific Lot
     */
    public function getLotLedger($lotNumber)
    {
        $lot = DB::table('lots')->where('lot_number', $lotNumber)->first();
        if (!$lot) {
            return response()->json(['error' => 'Lot not found'], 404);
        }

        $ledger = DB::table('inventory_ledger')
            ->where('lot_id', $lot->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($entry) {
                return [
                    'date' => Carbon::parse($entry->created_at)->format('d M · H:i'),
                    'action' => ucwords(str_replace('_', ' ', $entry->transaction_type)),
                    'detail' => $entry->notes,
                    'user' => $entry->user_name,
                    'document' => $entry->source_document,
                ];
            });

        return response()->json([
            'lot' => $lot->lot_number,
            'ledger' => $ledger,
        ]);
    }

    /**
     * Formulation Studio
     */
    public function getFormulation($code = 'FRM-SC-0042')
    {
        $formulation = DB::table('formulations')->where('code', $code)->first();
        if (!$formulation) {
            return response()->json(['error' => 'Formulation not found'], 404);
        }

        $lines = DB::table('formulation_lines')
            ->join('items', 'formulation_lines.item_id', '=', 'items.id')
            ->where('formulation_id', $formulation->id)
            ->select(
                'formulation_lines.phase',
                'items.inci_name as inci',
                'items.technical_name as tech',
                'formulation_lines.function',
                'formulation_lines.percentage',
                'formulation_lines.scale_qty'
            )
            ->orderBy('formulation_lines.id')
            ->get();

        $approvals = DB::table('formulation_approvals')
            ->where('formulation_id', $formulation->id)
            ->get()
            ->map(function ($app) {
                return [
                    'role' => $app->role_title,
                    'name' => $app->approver_name,
                    'initials' => $app->initials,
                    'signedAt' => Carbon::parse($app->signed_at)->format('d M · H:i'),
                ];
            });

        return response()->json([
            'code' => $formulation->code,
            'productName' => $formulation->product_name,
            'version' => $formulation->version,
            'status' => $formulation->status,
            'targetBatchSize' => $formulation->target_batch_size,
            'author' => $formulation->author_name,
            'effectiveDate' => Carbon::parse($formulation->effective_date)->format('d M Y'),
            'lines' => $lines,
            'approvals' => $approvals,
        ]);
    }

    /**
     * Shopfloor Dispensing Station
     */
    public function getDispensing($batchNumber = 'B-25-0415')
    {
        $batch = DB::table('production_batches')->where('batch_number', $batchNumber)->first();
        if (!$batch) {
            return response()->json(['error' => 'Batch not found'], 404);
        }

        $steps = DB::table('batch_dispense_steps')
            ->where('batch_id', $batch->id)
            ->orderBy('step_number')
            ->get();

        return response()->json([
            'batch' => [
                'batchNumber' => $batch->batch_number,
                'productName' => $batch->product_name,
                'targetSize' => '1,000 kg',
                'station' => 'WS-02',
                'scaleModel' => 'METTLER TOLEDO',
                'scaleStatus' => 'Connected',
            ],
            'steps' => $steps,
        ]);
    }

    /**
     * Confirm & Electronically Sign Dispense Step
     */
    public function signDispenseStep(Request $request)
    {
        $stepId = $request->input('stepId', 2);
        $actualWeight = $request->input('actualWeight', 50.042);
        $operator = $request->input('operator', 'Giulia Romano');

        DB::table('batch_dispense_steps')
            ->where('id', $stepId)
            ->update([
                'actual_weight' => $actualWeight,
                'operator_name' => $operator,
                'is_signed' => true,
                'signed_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => "Dispense step #{$stepId} signed successfully by {$operator}",
            'signedAt' => now()->format('d M Y · H:i:s'),
        ]);
    }

    /**
     * QC Laboratory Testing & Specs
     */
    public function getQualityReview($sampleNumber = 'QC-S-004182')
    {
        $sample = DB::table('qc_samples')->where('sample_number', $sampleNumber)->first();
        if (!$sample) {
            return response()->json(['error' => 'Sample not found'], 404);
        }

        $tests = DB::table('qc_test_results')->where('qc_sample_id', $sample->id)->get();

        return response()->json([
            'sample' => [
                'sampleNumber' => $sample->sample_number,
                'batchNumber' => $sample->batch_number,
                'productName' => $sample->product_name,
                'status' => $sample->status,
                'analyst' => $sample->analyst_name,
                'coaFilename' => $sample->coa_filename,
            ],
            'tests' => $tests,
        ]);
    }

    /**
     * QA 21 CFR Part 11 Electronic Signature Batch Release
     */
    public function releaseBatch(Request $request)
    {
        $batchNumber = $request->input('batchNumber', 'B-25-0398');
        $reason = $request->input('reason', 'Authorized for Commercial Distribution');
        $qaManager = $request->input('qaManager', 'Marco Conti · QA Manager');

        // Log QA release
        DB::table('qa_releases')->insert([
            'batch_number' => $batchNumber,
            'disposition' => 'Released',
            'qa_manager_name' => $qaManager,
            'approval_reason' => $reason,
            'signature_statement' => 'I understand that this electronic signature is the legal equivalent of my handwritten signature.',
            'released_at' => now(),
        ]);

        // Update QC Sample status
        DB::table('qc_samples')->where('batch_number', $batchNumber)->update([
            'status' => 'Approved',
            'updated_at' => now(),
        ]);

        // Update Production Batch status
        DB::table('production_batches')->where('batch_number', $batchNumber)->update([
            'status' => 'Released',
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Batch {$batchNumber} officially released for distribution",
            'releasedAt' => now()->format('d M Y · H:i:s T'),
        ]);
    }

    /**
     * Traceability Genealogy
     */
    public function getTraceability($lotNumber = 'FG-250415-A')
    {
        $record = DB::table('traceability_records')->where('finished_lot', $lotNumber)->first();

        return response()->json([
            'record' => $record,
            'stats' => [
                'materialsTraced' => 9,
                'productionRecords' => 1,
                'finishedUnits' => '18,400',
                'customersImpacted' => 2,
            ],
        ]);
    }
}
