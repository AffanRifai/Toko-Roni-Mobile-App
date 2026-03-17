<?php
// app/Http/Controllers/Api/ProductApiController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductApiController extends Controller
{
    /**
     * GET /api/v1/products
     */
    public function index(Request $request)
    {
        try {
            $query = Product::with('category')->where('is_active', true);

            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            $products = $query->latest()->paginate($request->get('per_page', 20));

            return response()->json([
                'success' => true,
                'data'    => $products,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/v1/products/search?q=keyword
     */
    public function search(Request $request)
    {
        try {
            $search     = $request->get('q', '');
            $categoryId = $request->get('category_id');
            $limit      = $request->get('limit', 10);

            $query = Product::with('category')->where('is_active', true);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name',    'like', "%{$search}%")
                        ->orWhere('code',    'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            }

            if ($categoryId && $categoryId !== 'all') {
                $query->where('category_id', $categoryId);
            }

            $products = $query->limit($limit)->get()->map(fn($p) => [
                'id'       => $p->id,
                'name'     => $p->name,
                'code'     => $p->code,
                'barcode'  => $p->barcode,
                'price'    => $p->price,
                'stock'    => $p->stock,
                'unit'     => $p->unit,
                'min_stock' => $p->min_stock ?? 10,
                'category' => ['name' => $p->category?->name ?? '-'],
                'image'    => $p->image ? asset('storage/' . $p->image) : null,
            ]);

            return response()->json(['success' => true, 'data' => $products]);
        } catch (\Exception $e) {
            Log::error('Product search error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/v1/products/low-stock
     * ⚠️ Method harus bernama lowStock sesuai route api.php
     */
    public function lowStock(Request $request)
    {
        try {
            $limit = $request->get('limit', 20);

            $products = Product::with('category')
                ->whereRaw('stock <= min_stock')
                ->where('is_active', true)
                ->orderBy('stock', 'asc')
                ->limit($limit)
                ->get()
                ->map(fn($p) => [
                    'id'        => $p->id,
                    'name'      => $p->name,
                    'stock'     => $p->stock,
                    'min_stock' => $p->min_stock ?? 10,
                    'unit'      => $p->unit,
                    'category'  => ['name' => $p->category?->name ?? '-'],
                ]);

            return response()->json(['success' => true, 'data' => $products]);
        } catch (\Exception $e) {
            Log::error('Low stock error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/v1/products/categories
     */
    public function categories()
    {
        try {
            $cats = Category::where('is_active', true)->withCount('products')->get();
            return response()->json(['success' => true, 'data' => $cats]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/v1/products/{product}
     */
    public function show(Product $product)
    {
        $product->load('category');
        return response()->json(['success' => true, 'data' => $product]);
    }

    /**
     * POST /api/v1/products
     */
    public function store(Request $request)
    {
        return response()->json(['success' => false, 'message' => 'Not implemented'], 501);
    }

    /**
     * PUT /api/v1/products/{product}
     */
    public function update(Request $request, Product $product)
    {
        return response()->json(['success' => false, 'message' => 'Not implemented'], 501);
    }

    /**
     * DELETE /api/v1/products/{product}
     */
    public function destroy(Product $product)
    {
        return response()->json(['success' => false, 'message' => 'Not implemented'], 501);
    }

    /**
     * POST /api/v1/products/{product}/update-stock
     */
    public function updateStock(Request $request, Product $product)
    {
        return response()->json(['success' => false, 'message' => 'Not implemented'], 501);
    }

    /**
     * POST /api/v1/products/{product}/toggle-active
     */
    public function toggleActive(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);
        return response()->json(['success' => true, 'is_active' => $product->is_active]);
    }

    /**
     * GET /api/v1/products/statistics/overview
     */
    public function getStatistics()
    {
        try {
            return response()->json([
                'success' => true,
                'data'    => [
                    'total'        => Product::count(),
                    'active'       => Product::where('is_active', true)->count(),
                    'low_stock'    => Product::whereRaw('stock <= min_stock AND stock > 0')->count(),
                    'out_of_stock' => Product::where('stock', '<=', 0)->count(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/v1/products/export/csv
     */
    public function export()
    {
        return response()->json(['success' => false, 'message' => 'Not implemented'], 501);
    }
}
