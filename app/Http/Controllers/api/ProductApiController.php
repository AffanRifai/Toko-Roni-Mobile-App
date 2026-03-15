<?php
// app/Http/Controllers/Api/ProductApiController.php
// Add these methods to your existing ProductController

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Search products
     */
    public function search(Request $request)
    {
        try {
            $search = $request->get('q', '');
            $categoryId = $request->get('category_id');
            $limit = $request->get('limit', 10);

            $query = Product::where('is_active', true);

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%")
                      ->orWhere('barcode', 'like', "%{$search}%");
                });
            }

            if ($categoryId && $categoryId !== 'all') {
                $query->where('category_id', $categoryId);
            }

            $products = $query->with('category')
                ->limit($limit)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'barcode' => $product->barcode,
                        'price' => $product->price,
                        'formatted_price' => 'Rp ' . number_format($product->price, 0, ',', '.'),
                        'stock' => $product->stock,
                        'unit' => $product->unit,
                        'category' => $product->category->name ?? 'Uncategorized',
                        'image' => $product->image ? asset('storage/' . $product->image) : null,
                        'stock_status' => $this->getStockStatus($product->stock),
                        'stock_badge' => $this->getStockBadge($product->stock),
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Search results retrieved successfully',
                'data' => $products
            ], 200);
        } catch (\Exception $e) {
            Log::error('Product search error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to search products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get low stock products
     */
    public function lowStockProducts(Request $request)
    {
        try {
            $threshold = $request->get('threshold', 10);
            $limit = $request->get('limit', 10);

            $products = Product::where('stock', '<=', $threshold)
                ->where('is_active', true)
                ->with('category')
                ->orderBy('stock', 'asc')
                ->limit($limit)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'stock' => $product->stock,
                        'unit' => $product->unit,
                        'min_stock' => $product->min_stock ?? 5,
                        'category' => $product->category->name ?? 'Uncategorized',
                        'status' => $product->stock <= 0 ? 'out_of_stock' : 'low_stock',
                        'status_text' => $product->stock <= 0 ? 'Habis' : 'Hampir Habis',
                        'status_badge' => $product->stock <= 0 ? 'danger' : 'warning',
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Low stock products retrieved successfully',
                'data' => $products
            ], 200);
        } catch (\Exception $e) {
            Log::error('Low stock products error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get low stock products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Quick view product details
     */
    public function quickView($id)
    {
        try {
            $product = Product::with(['category', 'supplier'])->find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            $data = [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'description' => $product->description,
                'price' => $product->price,
                'formatted_price' => 'Rp ' . number_format($product->price, 0, ',', '.'),
                'purchase_price' => $product->purchase_price,
                'formatted_purchase_price' => 'Rp ' . number_format($product->purchase_price, 0, ',', '.'),
                'stock' => $product->stock,
                'unit' => $product->unit,
                'min_stock' => $product->min_stock ?? 5,
                'category' => $product->category->name ?? 'Uncategorized',
                'supplier' => $product->supplier->name ?? '-',
                'image' => $product->image ? asset('storage/' . $product->image) : null,
                'is_active' => $product->is_active,
                'stock_status' => $this->getStockStatus($product->stock),
                'created_at' => $product->created_at->format('d/m/Y'),
                'last_updated' => $product->updated_at->format('d/m/Y H:i'),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Product details retrieved successfully',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            Log::error('Product quick view error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get product details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get stock status
     */
    private function getStockStatus($stock)
    {
        if ($stock <= 0) {
            return 'out_of_stock';
        } elseif ($stock <= 10) {
            return 'low_stock';
        } else {
            return 'in_stock';
        }
    }

    /**
     * Get stock badge class
     */
    private function getStockBadge($stock)
    {
        if ($stock <= 0) {
            return 'danger';
        } elseif ($stock <= 10) {
            return 'warning';
        } else {
            return 'success';
        }
    }
}
