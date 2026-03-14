<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Models\CheckerReport;
use App\Notifications\ProductReportedNotification;
use App\Notifications\ProductCreatedNotification;
use App\Notifications\ProductUpdatedNotification;
use App\Notifications\ProductDeletedNotification;
use App\Notifications\ProductStockNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        $query = Product::with('category')->latest();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('code', 'LIKE', "%{$search}%")
                    ->orWhere('barcode', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Status filter
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Stock filter
        if ($request->filled('stock')) {
            if ($request->stock == 'low') {
                $query->whereRaw('stock <= min_stock AND stock > 0');
            } elseif ($request->stock == 'out') {
                $query->where('stock', '<=', 0);
            } elseif ($request->stock == 'normal') {
                $query->whereRaw('stock > min_stock');
            }
        }

        // Get statistics for dashboard
        $stats = [
            'total' => Product::count(),
            'active' => Product::where('is_active', true)->count(),
            'low_stock' => Product::whereRaw('stock <= min_stock AND stock > 0')->count(),
            'out_of_stock' => Product::where('stock', '<=', 0)->count(),
            'total_value' => Product::sum(DB::raw('stock * price')),
            'total_profit' => Product::sum(DB::raw('stock * (price - cost_price)')),
        ];

        // EXPIRY STATISTICS
        $today = Carbon::today();

        // Expiry filter
        if ($request->filled('expiry')) {
            if ($request->expiry == 'expiring') {
                $query->whereNotNull('expiry_date')
                    ->where('expiry_date', '<=', $today->copy()->addDays(30))
                    ->where('expiry_date', '>', $today);
            } elseif ($request->expiry == 'expired') {
                $query->whereNotNull('expiry_date')
                    ->where('expiry_date', '<', $today);
            }
        }

        // Produk yang akan kadaluarsa dalam 30 hari
        $expiringSoonCount = Product::whereNotNull('expiry_date')
            ->where('expiry_date', '<=', $today->copy()->addDays(30))
            ->where('expiry_date', '>', $today)
            ->count();

        // Produk yang sudah kadaluarsa
        $expiredCount = Product::whereNotNull('expiry_date')
            ->where('expiry_date', '<', $today)
            ->count();

        // Daftar produk akan kadaluarsa (untuk ditampilkan di card)
        $expiringProducts = Product::with('category')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', $today->copy()->addDays(30))
            ->where('expiry_date', '>', $today)
            ->orderBy('expiry_date', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($product) use ($today) {
                $expiry = Carbon::parse($product->expiry_date);
                $diffInDays = $today->diffInDays($expiry, false);
                $product->days_left = (int) floor($diffInDays);
                return $product;
            });

        // Daftar produk kadaluarsa (untuk ditampilkan di card)
        $expiredProducts = Product::with('category')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', $today)
            ->orderBy('expiry_date', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($product) use ($today) {
                $diffInDays = Carbon::parse($product->expiry_date)->diffInDays($today, false);
                $product->days_expired = (int) floor($diffInDays);
                return $product;
            });

        $perPage = $request->per_page ?? 20;
        $products = $query->paginate($perPage);
        
        // Tambahkan status expired untuk setiap produk di pagination
        $products->getCollection()->transform(function ($product) use ($today) {
            if ($product->expiry_date) {
                $expiry = Carbon::parse($product->expiry_date);
                $product->is_expired = $expiry < $today;
                $diffInDays = $expiry->diffInDays($today, false);
                $product->days_until_expiry = (int) floor($diffInDays);
            } else {
                $product->is_expired = false;
                $product->days_until_expiry = null;
            }
            return $product;
        });

        $categories = Category::where('is_active', true)
            ->withCount('products')
            ->get();

        return view('products.index', compact(
            'products',
            'categories',
            'stats',
            'expiringSoonCount',
            'expiredCount',
            'expiringProducts',
            'expiredProducts'
        ));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        return view('products.create', compact('categories'));
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:products',
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:1',
            'cost_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'unit' => 'required|string|max:20',
            'barcode' => 'nullable|string|max:100|unique:products',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|string|max:100',
            'expiry_date' => 'nullable|date',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'is_active' => 'boolean',
        ]);

        // Set default min_stock if not provided
        if (empty($validated['min_stock'])) {
            $validated['min_stock'] = 10;
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        // Calculate profit
        $profit = $validated['price'] - $validated['cost_price'];
        $validated['profit_margin'] = $validated['cost_price'] > 0 ? ($profit / $validated['cost_price']) * 100 : 0;
        $validated['total_value'] = $validated['stock'] * $validated['price'];
        $validated['total_cost'] = $validated['stock'] * $validated['cost_price'];
        $validated['total_profit'] = $validated['stock'] * $profit;

        // Set created_by
        $validated['created_by'] = auth()->id();

        try {
            DB::beginTransaction();

            $product = Product::create($validated);

            // KIRIM NOTIFIKASI PRODUK BARU
            $this->sendProductCreatedNotifications($product);

            // CEK STOK RENDAH
            $this->checkProductStock($product);

            DB::commit();

            return redirect()->route('products.index')
                ->with('success', 'Produk "' . $product->name . '" berhasil ditambahkan!')
                ->with('new_product_id', $product->id);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating product: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan produk: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        $product->load('category', 'createdBy', 'updatedBy');

        // Get related products
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->limit(4)
            ->get();

        // Get stock history
        $stockHistory = DB::table('stock_histories')
            ->where('product_id', $product->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('products.show', compact('product', 'relatedProducts', 'stockHistory'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        $categories = Category::where('is_active', true)->get();
        return view('products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, Product $product)
    {
        // Bersihkan format angka sebelum validasi
        if ($request->has('price')) {
            $request->merge([
                'price' => $this->parseIndonesianNumber($request->price)
            ]);
        }
        
        if ($request->has('cost_price')) {
            $request->merge([
                'cost_price' => $this->parseIndonesianNumber($request->cost_price)
            ]);
        }

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:products,code,' . $product->id,
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:1',
            'cost_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'unit' => 'required|string|max:20',
            'barcode' => 'nullable|string|max:100|unique:products,barcode,' . $product->id,
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|string|max:100',
            'expiry_date' => 'nullable|date',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'is_active' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            // Handle image update
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
                $validated['image'] = $request->file('image')->store('products', 'public');
            }

            // Catat perubahan sebelum update
            $oldData = $product->toArray();
            $oldStock = $product->stock;

            // Calculate updated profit metrics
            $profit = $validated['price'] - $validated['cost_price'];
            $validated['profit_margin'] = $validated['cost_price'] > 0 ? ($profit / $validated['cost_price']) * 100 : 0;
            $validated['total_value'] = $validated['stock'] * $validated['price'];
            $validated['total_cost'] = $validated['stock'] * $validated['cost_price'];
            $validated['total_profit'] = $validated['stock'] * $profit;

            // Set updated_by
            $validated['updated_by'] = auth()->id();

            // Catat perubahan untuk notifikasi
            $changes = [];
            $skipFields = ['image', 'updated_at', 'created_at', 'profit_margin', 'total_value', 'total_cost', 'total_profit'];
            
            foreach ($validated as $key => $value) {
                if (in_array($key, $skipFields)) continue;
                
                if (isset($oldData[$key]) && $oldData[$key] != $value) {
                    $changes[$key] = [
                        'old' => $oldData[$key],
                        'new' => $value
                    ];
                }
            }

            $product->update($validated);

            // Record stock change if stock was modified
            if ($oldStock != $validated['stock']) {
                $changeType = $validated['stock'] > $oldStock ? 'add' : 'reduce';
                $quantity = abs($validated['stock'] - $oldStock);

                DB::table('stock_histories')->insert([
                    'product_id' => $product->id,
                    'type' => $changeType,
                    'quantity' => $quantity,
                    'previous_stock' => $oldStock,
                    'new_stock' => $validated['stock'],
                    'note' => 'Manual update via edit form',
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Cek stok setelah perubahan
                $this->checkProductStock($product);
            }

            // Kirim notifikasi jika ada perubahan
            if (!empty($changes)) {
                $this->sendProductUpdatedNotifications($product, $changes);
            }

            DB::commit();

            return redirect()->route('products.index')
                ->with('success', 'Produk "' . $product->name . '" berhasil diperbarui!')
                ->with('updated_product_id', $product->id);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating product: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui produk: ' . $e->getMessage());
        }
    }

    /**
     * Parse angka dengan format Indonesia ke format numerik
     */
    private function parseIndonesianNumber($value)
    {
        if (is_null($value) || $value === '') {
            return 0;
        }
        
        // Hapus "Rp" dan spasi
        $value = str_replace(['Rp', 'rp', ' '], '', $value);
        
        // Hapus titik ribuan
        $value = str_replace('.', '', $value);
        
        // Ganti koma desimal dengan titik
        $value = str_replace(',', '.', $value);
        
        return (float) $value;
    }

    /**
     * Remove the specified product.
     */
    public function destroy(Product $product)
    {
        try {
            DB::beginTransaction();

            $productName = $product->name;
            $productCode = $product->code;
            $currentUser = auth()->user();

            // Delete image if exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $product->delete();

            // Kirim notifikasi penghapusan
            $this->sendProductDeletedNotifications($productName, $productCode, $currentUser);

            DB::commit();

            return redirect()->route('products.index')
                ->with('success', 'Produk "' . $productName . '" berhasil dihapus!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting product: ' . $e->getMessage());
            return redirect()->route('products.index')
                ->with('error', 'Gagal menghapus produk: ' . $e->getMessage());
        }
    }

    /**
     * Quick view product details (AJAX)
     */
    public function quickView($id)
    {
        $product = Product::with('category')->findOrFail($id);

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'code' => $product->code,
            'price' => $product->price,
            'stock' => $product->stock,
            'unit' => $product->unit,
            'cost_price' => $product->cost_price,
            'description' => $product->description,
            'image_url' => $product->image ? Storage::url($product->image) : null,
            'category_name' => $product->category->name ?? null,
            'expiry_date' => $product->expiry_date ? $product->expiry_date->format('Y-m-d') : null,
            'expiry_status' => $product->expiry_date ? $this->getExpiryStatus($product->expiry_date) : null,
            'min_stock' => $product->min_stock,
            'barcode' => $product->barcode,
            'weight' => $product->weight,
            'dimensions' => $product->dimensions,
        ]);
    }

    /**
     * Update stock (AJAX)
     */
    public function updateStock(Request $request, Product $product)
    {
        $request->validate([
            'type' => 'required|in:add,reduce,adjust',
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            $previousStock = $product->stock;
            $oldStock = $product->stock;

            if ($request->type == 'add') {
                $product->increment('stock', $request->quantity);
            } elseif ($request->type == 'reduce') {
                if ($product->stock < $request->quantity) {
                    throw new \Exception('Stok tidak mencukupi untuk pengurangan');
                }
                $product->decrement('stock', $request->quantity);
            } elseif ($request->type == 'adjust') {
                $product->stock = $request->quantity;
                $product->save();
            }

            $product->refresh();

            // Record stock history
            DB::table('stock_histories')->insert([
                'product_id' => $product->id,
                'type' => $request->type,
                'quantity' => $request->quantity,
                'previous_stock' => $previousStock,
                'new_stock' => $product->stock,
                'note' => $request->note,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update updated_by
            $product->update(['updated_by' => auth()->id()]);

            // Cek stok setelah update
            if ($product->stock == 0) {
                $this->sendStockNotifications($product, 'out_of_stock');
            } elseif ($product->stock <= $product->min_stock) {
                $this->sendStockNotifications($product, 'low_stock');
            } elseif ($product->stock > $oldStock) {
                $this->sendStockNotifications($product, 'restock');
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stok berhasil diperbarui!',
                'new_stock' => $product->stock
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui stok: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle product status
     */
    public function toggleStatus(Product $product)
    {
        $oldStatus = $product->is_active;
        $product->update(['is_active' => !$product->is_active]);

        $status = $product->is_active ? 'diaktifkan' : 'dinonaktifkan';
        
        // Kirim notifikasi perubahan status
        if ($oldStatus != $product->is_active) {
            $changes = [
                'is_active' => [
                    'old' => $oldStatus,
                    'new' => $product->is_active
                ]
            ];
            $this->sendProductUpdatedNotifications($product, $changes);
        }

        return back()->with('success', 'Produk berhasil ' . $status . '!');
    }

    /**
     * Export products
     */
    public function export(Request $request)
    {
        return response()->json(['message' => 'Export feature coming soon']);
    }

    /**
     * Quick edit product (AJAX)
     */
    public function quickEdit(Request $request, Product $product)
    {
        $request->validate([
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $oldPrice = $product->price;
        $oldStock = $product->stock;
        
        $product->update($request->only(['price', 'stock']));

        // Catat perubahan untuk notifikasi
        $changes = [];
        if ($oldPrice != $request->price) {
            $changes['price'] = [
                'old' => $oldPrice,
                'new' => $request->price
            ];
        }
        if ($oldStock != $request->stock) {
            $changes['stock'] = [
                'old' => $oldStock,
                'new' => $request->stock
            ];
        }

        if (!empty($changes)) {
            $this->sendProductUpdatedNotifications($product, $changes);
        }

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil diperbarui',
            'product' => $product->fresh()
        ]);
    }

    /**
     * Get product data (AJAX)
     */
    public function getProductData(Product $product)
    {
        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'category' => $product->category?->name,
                'price' => number_format($product->price, 0, ',', '.'),
                'cost_price' => number_format($product->cost_price, 0, ',', '.'),
                'stock' => $product->stock,
                'min_stock' => $product->min_stock,
                'unit' => $product->unit,
                'profit_margin' => round($product->profit_margin, 2),
                'total_value' => number_format($product->total_value, 0, ',', '.'),
                'image_url' => $product->image ? asset('storage/' . $product->image) : null,
                'description' => $product->description,
                'barcode' => $product->barcode,
                'weight' => $product->weight,
                'dimensions' => $product->dimensions,
                'expiry_date' => $product->expiry_date?->format('d/m/Y'),
                'expiry_status' => $product->expiry_date ? $this->getExpiryStatus($product->expiry_date) : null,
                'created_at' => $product->created_at->format('d/m/Y H:i'),
                'status' => $product->is_active ? 'Aktif' : 'Nonaktif',
            ]
        ]);
    }

    /**
     * Get expired products (API)
     */
    public function getExpiredProducts()
    {
        $today = Carbon::today();

        $expired = Product::with('category')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', $today)
            ->orderBy('expiry_date', 'desc')
            ->get()
            ->map(function ($product) use ($today) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code,
                    'stock' => $product->stock,
                    'unit' => $product->unit,
                    'expiry_date' => $product->expiry_date->format('d/m/Y'),
                    'days_expired' => (int) floor(Carbon::parse($product->expiry_date)->diffInDays($today, false)),
                    'category' => $product->category?->name,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $expired
        ]);
    }

    /**
     * Get expiring products (API)
     */
    public function getExpiringProducts()
    {
        $today = Carbon::today();

        $expiring = Product::with('category')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', $today->copy()->addDays(30))
            ->where('expiry_date', '>', $today)
            ->orderBy('expiry_date', 'asc')
            ->get()
            ->map(function ($product) use ($today) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code,
                    'stock' => $product->stock,
                    'unit' => $product->unit,
                    'expiry_date' => $product->expiry_date->format('d/m/Y'),
                    'days_left' => (int) floor($today->diffInDays($product->expiry_date, false)),
                    'category' => $product->category?->name,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $expiring
        ]);
    }

    // ==================== PRIVATE METHODS ====================

    /**
     * Get expiry status
     */
    private function getExpiryStatus($expiryDate)
    {
        $today = Carbon::today();
        $expiry = Carbon::parse($expiryDate);

        if ($expiry < $today) {
            return 'expired';
        } elseif ($expiry <= $today->copy()->addDays(7)) {
            return 'critical';
        } elseif ($expiry <= $today->copy()->addDays(30)) {
            return 'warning';
        } else {
            return 'good';
        }
    }

    /**
     * Send notifications when product is created
     */
    private function sendProductCreatedNotifications($product)
    {
        try {
            $currentUser = auth()->user();
            
            // Kirim ke semua user dengan role owner
            $owners = User::where('role', 'owner')->get();
            
            foreach ($owners as $owner) {
                if ($owner->id != $currentUser->id) {
                    $owner->notify(new ProductCreatedNotification($product, $currentUser));
                    Log::info('Notifikasi produk terkirim ke owner:', [
                        'owner_id' => $owner->id,
                        'product_id' => $product->id
                    ]);
                }
            }
            
            // Kirim ke user dengan role gudang
            $gudang = User::where('role', 'gudang')->get();
            
            foreach ($gudang as $user) {
                if ($user->id != $currentUser->id) {
                    $user->notify(new ProductCreatedNotification($product, $currentUser));
                    Log::info('Notifikasi produk terkirim ke gudang:', [
                        'user_id' => $user->id,
                        'product_id' => $product->id
                    ]);
                }
            }
            
            // Kirim ke diri sendiri (pembuat)
            $currentUser->notify(new ProductCreatedNotification($product, $currentUser));
            Log::info('Notifikasi produk terkirim ke diri sendiri:', [
                'user_id' => $currentUser->id,
                'product_id' => $product->id
            ]);
            
        } catch (\Exception $e) {
            Log::error('Gagal mengirim notifikasi produk: ' . $e->getMessage());
        }
    }

    /**
     * Check product stock and send notifications if low/out of stock
     */
    private function checkProductStock($product)
    {
        try {
            if ($product->stock == 0) {
                $this->sendStockNotifications($product, 'out_of_stock');
            } elseif ($product->stock <= $product->min_stock) {
                $this->sendStockNotifications($product, 'low_stock');
            }
        } catch (\Exception $e) {
            Log::error('Gagal cek stok produk: ' . $e->getMessage());
        }
    }

    /**
     * Send stock notifications to relevant users
     */
    private function sendStockNotifications($product, $type)
    {
        try {
            $users = User::whereIn('role', ['owner', 'gudang'])->get();
            
            foreach ($users as $user) {
                $user->notify(new ProductStockNotification($product, $type));
                Log::info('Notifikasi stok ' . $type . ' terkirim ke:', [
                    'user_id' => $user->id,
                    'product_id' => $product->id
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Gagal mengirim notifikasi stok: ' . $e->getMessage());
        }
    }

    /**
     * Send notifications when product is updated
     */
    private function sendProductUpdatedNotifications($product, $changes)
    {
        try {
            $currentUser = auth()->user();
            
            $owners = User::where('role', 'owner')
                ->where('id', '!=', $currentUser->id)
                ->get();
            
            foreach ($owners as $owner) {
                $owner->notify(new ProductUpdatedNotification($product, $currentUser, $changes));
                Log::info('Notifikasi update produk terkirim ke owner:', [
                    'owner_id' => $owner->id,
                    'product_id' => $product->id
                ]);
            }
            
            $gudang = User::where('role', 'gudang')
                ->where('id', '!=', $currentUser->id)
                ->get();
            
            foreach ($gudang as $user) {
                $user->notify(new ProductUpdatedNotification($product, $currentUser, $changes));
                Log::info('Notifikasi update produk terkirim ke gudang:', [
                    'user_id' => $user->id,
                    'product_id' => $product->id
                ]);
            }
            
            $currentUser->notify(new ProductUpdatedNotification($product, $currentUser, $changes));
            Log::info('Notifikasi update produk terkirim ke diri sendiri:', [
                'user_id' => $currentUser->id,
                'product_id' => $product->id
            ]);
            
        } catch (\Exception $e) {
            Log::error('Gagal mengirim notifikasi update produk: ' . $e->getMessage());
        }
    }

    /**
     * Send notifications when product is deleted
     */
    private function sendProductDeletedNotifications($productName, $productCode, $deletedBy)
    {
        try {
            $owners = User::where('role', 'owner')
                ->where('id', '!=', $deletedBy->id)
                ->get();
            
            foreach ($owners as $owner) {
                $owner->notify(new ProductDeletedNotification($productName, $productCode, $deletedBy));
                Log::info('Notifikasi hapus produk terkirim ke owner:', [
                    'owner_id' => $owner->id,
                    'product_name' => $productName
                ]);
            }
            
            $gudang = User::where('role', 'gudang')
                ->where('id', '!=', $deletedBy->id)
                ->get();
            
            foreach ($gudang as $user) {
                $user->notify(new ProductDeletedNotification($productName, $productCode, $deletedBy));
                Log::info('Notifikasi hapus produk terkirim ke gudang:', [
                    'user_id' => $user->id,
                    'product_name' => $productName
                ]);
            }
            
            $deletedBy->notify(new ProductDeletedNotification($productName, $productCode, $deletedBy));
            Log::info('Notifikasi hapus produk terkirim ke diri sendiri:', [
                'user_id' => $deletedBy->id,
                'product_name' => $productName
            ]);
            
        } catch (\Exception $e) {
            Log::error('Gagal mengirim notifikasi hapus produk: ' . $e->getMessage());
        }
    }

    /**
 * Report product to kepala gudang (for checker)
 */
public function reportProduct(Request $request, Product $product)
{
    $request->validate([
        'report_type' => 'required|in:low_stock,expiring,expired,damaged,other',
        'notes' => 'required|string|max:500',
        'quantity' => 'nullable|integer|min:1',
    ]);

    try {
        DB::beginTransaction();

        // Buat laporan
        $report = CheckerReport::create([
            'product_id' => $product->id,
            'reported_by' => auth()->id(),
            'report_type' => $request->report_type,
            'notes' => $request->notes,
            'quantity' => $request->quantity ?? $product->stock,
            'status' => 'pending',
            'reported_at' => now(),
        ]);

        // Kirim notifikasi ke semua kepala gudang
        $kepalaGudang = User::where('role', 'kepala_gudang')->get();
        
        foreach ($kepalaGudang as $kg) {
            $kg->notify(new ProductReportedNotification($report, $product, auth()->user()));
        }

        // Kirim juga ke owner dan manager
        $owners = User::whereIn('role', ['owner', 'manager'])->get();
        
        foreach ($owners as $owner) {
            $owner->notify(new ProductReportedNotification($report, $product, auth()->user()));
        }

        DB::commit();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Laporan berhasil dikirim ke Kepala Gudang',
                'report' => $report
            ]);
        }

        return redirect()->back()->with('success', 'Laporan berhasil dikirim ke Kepala Gudang');

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error reporting product: ' . $e->getMessage());

        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim laporan: ' . $e->getMessage()
            ], 500);
        }

        return redirect()->back()->with('error', 'Gagal mengirim laporan: ' . $e->getMessage());
    }
}
}