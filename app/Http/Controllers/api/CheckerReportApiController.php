<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CheckerReport;
use App\Models\Product;
use App\Models\User;
use App\Notifications\ProductReportedNotification;
use App\Services\NotificationRoutingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckerReportApiController extends Controller
{
    /**
     * GET /api/v1/checker/reports
     */
    public function index(Request $request): JsonResponse
    {
        try {
            /** @var User $actor */
            $actor = $request->user();
            $limit = max(1, min((int) $request->get('limit', 20), 100));

            $query = CheckerReport::query()
                ->with(['product.category', 'reportedBy'])
                ->latest();

            $status = strtolower(trim((string) $request->get('status', '')));
            if ($status !== '' && $status !== 'all') {
                $query->where('status', $status);
            }

            $reportType = strtolower(trim((string) $request->get('report_type', '')));
            if ($reportType !== '' && $reportType !== 'all') {
                $query->where('report_type', $reportType);
            }

            if (!$this->isSupervisorRole($actor->role)) {
                $actorRole = strtolower(trim((string) $actor->role));
                $query->where(function ($q) use ($actor, $actorRole) {
                    $q->where('reported_by', $actor->id);
                    if ($actorRole !== '') {
                        $q->orWhereHas('reportedBy', function ($sub) use ($actorRole) {
                            $sub->whereRaw('LOWER(role) = ?', [$actorRole]);
                        });
                    }
                });
            }

            $reports = $query
                ->limit($limit)
                ->get()
                ->map(fn (CheckerReport $report) => $this->formatReport($report))
                ->values();

            return response()->json([
                'success' => true,
                'message' => 'Laporan checker berhasil dimuat.',
                'data' => $reports,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Checker report index API error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat laporan checker.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * POST /api/v1/checker/reports
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'report_type' => 'required|in:low_stock,expiring,expired,damaged,other',
            'notes' => 'required|string|min:6|max:1000',
            'quantity' => 'nullable|integer|min:1',
            'priority' => 'nullable|string',
            'urgent' => 'nullable|boolean',
        ]);

        try {
            /** @var User $actor */
            $actor = $request->user();
            /** @var Product $product */
            $product = Product::with('category')->findOrFail($validated['product_id']);

            DB::beginTransaction();

            /** @var CheckerReport $report */
            $report = CheckerReport::create([
                'product_id' => $product->id,
                'reported_by' => $actor->id,
                'report_type' => $validated['report_type'],
                'notes' => trim((string) $validated['notes']),
                'quantity' => $validated['quantity'] ?? $product->stock,
                'status' => 'pending',
                'reported_at' => now(),
            ]);

            $report->load(['product.category', 'reportedBy']);
            $this->notifyUrgentRecipients($report, $product, $actor);

            DB::commit();

            $formatted = $this->formatReport($report);

            return response()->json([
                'success' => true,
                'message' => 'Laporan checker berhasil dikirim.',
                'data' => $formatted,
                'report' => $formatted,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Checker report store API error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim laporan checker.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * POST /api/v1/products/{product}/report
     */
    public function storeForProduct(Request $request, Product $product): JsonResponse
    {
        $request->merge(['product_id' => $product->id]);
        return $this->store($request);
    }

    private function notifyUrgentRecipients(CheckerReport $report, Product $product, User $actor): void
    {
        try {
            /** @var NotificationRoutingService $router */
            $router = app(NotificationRoutingService::class);
            $meta = $router->metaForEvent('checker.product_reported');

            $router->send(
                'checker.product_reported',
                new ProductReportedNotification($report, $product, $actor, $meta),
                $actor
            );
        } catch (\Throwable $e) {
            Log::warning('Checker report notification failed: ' . $e->getMessage());
        }
    }

    private function isSupervisorRole(?string $role): bool
    {
        $normalized = strtolower(trim((string) $role));
        return in_array($normalized, ['owner', 'manager', 'kepala_gudang'], true);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatReport(CheckerReport $report): array
    {
        $product = $report->product;

        return [
            'id' => (string) $report->id,
            'product_id' => $report->product_id,
            'product_name' => $product?->name ?? '-',
            'category' => $product?->category?->name ?? '-',
            'report_type' => $report->report_type,
            'notes' => $report->notes,
            'quantity' => $report->quantity,
            'status' => $report->status,
            'is_synced' => true,
            'reported_by' => $report->reported_by,
            'reported_by_name' => $report->reportedBy?->name,
            'created_at' => optional($report->created_at)?->toISOString(),
            'updated_at' => optional($report->updated_at)?->toISOString(),
            'reported_at' => optional($report->reported_at)?->toISOString(),
        ];
    }
}
