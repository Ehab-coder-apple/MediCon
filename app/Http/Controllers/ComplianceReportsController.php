<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ComplianceReportsController
 *
 * Isolated, read-only controller for the compliance / regulatory and insurance
 * reports surfaced on the Reports page. It never writes to the database and does
 * not modify any existing model, migration, or business logic. Reports that have
 * no source data yet render a structured blank layout ready for future data;
 * reports with an existing data source query it read-only. Every action is
 * defensively wrapped so a missing table/column can never break the page.
 */
class ComplianceReportsController extends Controller
{
    /**
     * Controlled Substances / Narcotics Log.
     *
     * No controlled-substance flag exists on products yet, so this renders a
     * structured, empty regulatory log ready to be populated once a controlled
     * flag / prescriber field is added.
     */
    public function narcoticsLog(Request $request): View
    {
        $this->authorize('access-admin-dashboard');

        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        $entries = collect();

        return view('reports.compliance.narcotics', compact('entries', 'startDate', 'endDate'));
    }

    /**
     * DOH Compliance Export.
     *
     * Presents an export-ready summary built from real expiry data in the
     * batches table (expired and near-expiry stock), plus a date range for a
     * future formal export.
     */
    public function dohExport(Request $request): View
    {
        $this->authorize('access-admin-dashboard');

        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        $summary = [
            'expired_batches' => 0,
            'nearly_expired_batches' => 0,
            'total_active_batches' => 0,
        ];

        try {
            $summary['expired_batches'] = Batch::where('expiry_date', '<', now())
                ->where('quantity', '>', 0)
                ->count();

            $summary['nearly_expired_batches'] = Batch::whereBetween('expiry_date', [now(), now()->copy()->addDays(30)])
                ->where('quantity', '>', 0)
                ->count();

            $summary['total_active_batches'] = Batch::where('quantity', '>', 0)->count();
        } catch (\Throwable $e) {
            // Leave zeroed summary if the batches table is unavailable.
        }

        return view('reports.compliance.doh-export', compact('summary', 'startDate', 'endDate'));
    }

    /**
     * Inventory Wastage & Disposal Log.
     *
     * Lists expired stock still on hand (quantity > 0) as disposal candidates,
     * read-only from the batches table.
     */
    public function wastageLog(Request $request): View
    {
        $this->authorize('access-admin-dashboard');

        $batches = collect();
        $summary = ['batch_count' => 0, 'total_quantity' => 0, 'total_value' => 0];

        try {
            $batches = Batch::with('product')
                ->where('expiry_date', '<', now())
                ->where('quantity', '>', 0)
                ->orderBy('expiry_date')
                ->paginate(25);

            $agg = Batch::where('expiry_date', '<', now())
                ->where('quantity', '>', 0)
                ->selectRaw('COUNT(*) as batch_count, COALESCE(SUM(quantity), 0) as total_quantity, COALESCE(SUM(quantity * cost_price), 0) as total_value')
                ->first();

            $summary = [
                'batch_count' => (int) ($agg->batch_count ?? 0),
                'total_quantity' => (int) ($agg->total_quantity ?? 0),
                'total_value' => (float) ($agg->total_value ?? 0),
            ];
        } catch (\Throwable $e) {
            // Fall back to the empty state.
        }

        return view('reports.compliance.wastage', compact('batches', 'summary'));
    }

    /**
     * Insurance Claims Settlement.
     *
     * No insurance data source exists yet; renders a structured blank layout
     * ready for future data.
     */
    public function insuranceClaims(Request $request): View
    {
        $this->authorize('access-admin-dashboard');

        $claims = collect();

        return view('reports.compliance.insurance-claims', compact('claims'));
    }

    /**
     * Insurance Rejection Log.
     *
     * No insurance data source exists yet; renders a structured blank layout
     * ready for future data.
     */
    public function insuranceRejections(Request $request): View
    {
        $this->authorize('access-admin-dashboard');

        $rejections = collect();

        return view('reports.compliance.insurance-rejections', compact('rejections'));
    }
}
