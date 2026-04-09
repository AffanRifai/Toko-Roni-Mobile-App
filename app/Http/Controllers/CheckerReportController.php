<?php
// app/Http/Controllers/CheckerReportController.php

namespace App\Http\Controllers;

use App\Models\CheckerReport;
use App\Models\User;
use App\Notifications\ReportResolvedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckerReportController extends Controller
{
    /**
     * Display a listing of reports.
     */
    public function index(Request $request)
    {
        $query = CheckerReport::with(['product', 'reportedBy', 'resolvedBy']);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('report_type', $request->type);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })->orWhere('notes', 'like', "%{$search}%");
        }

        $reports = $query->latest()->paginate(20);

        $stats = [
            'total' => CheckerReport::count(),
            'pending' => CheckerReport::where('status', 'pending')->count(),
            'in_progress' => CheckerReport::where('status', 'in_progress')->count(),
            'resolved' => CheckerReport::where('status', 'resolved')->count(),
        ];

        return view('checker-reports.index', compact('reports', 'stats'));
    }

    /**
     * Mark report as resolved.
     */
    public function resolve(Request $request, CheckerReport $report)
    {
        $request->validate([
            'resolution_notes' => 'required|string|max:500',
        ]);

        DB::beginTransaction();

        try {
            $oldStatus = $report->status;
            
            $report->update([
                'status' => 'resolved',
                'resolved_by' => auth()->id(),
                'resolved_at' => now(),
                'resolution_notes' => $request->resolution_notes,
            ]);

            // Kirim notifikasi ke checker yang melapor
            $checker = User::find($report->reported_by);
            if ($checker) {
                $checker->notify(new ReportResolvedNotification($report, auth()->user()));
            }

            DB::commit();

            return redirect()->route('checker.index')
                ->with('success', 'Laporan berhasil diselesaikan');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error resolving report: ' . $e->getMessage());
            return back()->with('error', 'Gagal menyelesaikan laporan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified report.
     */
    public function destroy(CheckerReport $report)
    {
        try {
            $report->delete();
            return redirect()->route('checker.index')
                ->with('success', 'Laporan berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Error deleting report: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus laporan: ' . $e->getMessage());
        }
    }

    public function allReports(Request $request)
{
    $query = CheckerReport::with(['product', 'reportedBy', 'resolvedBy']);

    // Filter by status
    if ($request->has('status') && $request->status !== 'all') {
        $query->where('status', $request->status);
    }

    // Filter by type
    if ($request->has('type') && $request->type !== 'all') {
        $query->where('report_type', $request->type);
    }

    $reports = $query->latest()->paginate(20);

    return view('checker-reports.all', compact('reports'));
}

}