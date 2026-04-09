<?php
/** @noinspection PhpUndefinedMethodInspection */
/** @noinspection PhpUndefinedVariableInspection */

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\User;
use App\Notifications\CategoryCreatedNotification;
use App\Notifications\CategoryUpdatedNotification;
use App\Notifications\CategoryDeletedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('name')->paginate(20);
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        // Log request untuk debugging
        \Log::info('Category store request received', [
            'all' => $request->all(),
            'method' => $request->method(),
            'url' => $request->url(),
            'ajax' => $request->ajax()
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
            'description' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        try {
            DB::beginTransaction();

            $validated['slug'] = Str::slug($validated['name']);
            $validated['is_active'] = $request->has('is_active') ? (bool)$request->is_active : true;

            \Log::info('Creating category with data', $validated);

            $category = Category::create($validated);

            \Log::info('Category created successfully', ['id' => $category->id, 'name' => $category->name]);

            // KIRIM NOTIFIKASI
            $this->sendCategoryCreatedNotifications($category);

            DB::commit();

            // Jika AJAX request, return JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Kategori "' . $category->name . '" berhasil ditambahkan!',
                    'category' => $category
                ]);
            }

            return redirect()->route('products.index')
                ->with('success', 'Kategori "' . $category->name . '" berhasil ditambahkan!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Category creation failed: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan kategori: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('products.index')
                ->with('error', 'Gagal menambahkan kategori: ' . $e->getMessage());
        }
    }

    /**
     * Send notifications when category is created
     */
    private function sendCategoryCreatedNotifications($category)
    {
        try {
            $currentUser = auth()->user();
            
            // 1. Kirim ke semua user dengan role owner
            $owners = User::where('role', 'owner')->get();
            
            foreach ($owners as $owner) {
                if ($owner->id != $currentUser->id) {
                    $owner->notify(new CategoryCreatedNotification($category, $currentUser));
                    Log::info('Notifikasi kategori terkirim ke owner:', [
                        'owner_id' => $owner->id,
                        'category_id' => $category->id
                    ]);
                }
            }
            
            // 2. Kirim ke diri sendiri (pembuat)
            $currentUser->notify(new CategoryCreatedNotification($category, $currentUser));
            Log::info('Notifikasi kategori terkirim ke diri sendiri:', [
                'user_id' => $currentUser->id,
                'category_id' => $category->id
            ]);
            
        } catch (\Exception $e) {
            Log::error('Gagal mengirim notifikasi kategori: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified category.
     */
    public function show(Category $category)
    {
        // Load category dengan relasi products
        $category->load('products');
        
        // Hitung jumlah produk dalam kategori ini
        $productsCount = $category->products()->count();
        
        return view('categories.show', compact('category', 'productsCount'));
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name,' . $category->id,
            'description' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Catat perubahan sebelum update
            $oldData = $category->toArray();
            
            $validated['slug'] = Str::slug($validated['name']);
            $validated['is_active'] = $request->has('is_active');

            // Catat perubahan
            $changes = [];
            foreach ($validated as $key => $value) {
                if (isset($oldData[$key]) && $oldData[$key] != $value) {
                    $changes[$key] = [
                        'old' => $oldData[$key],
                        'new' => $value
                    ];
                }
            }

            $category->update($validated);

            // Kirim notifikasi jika ada perubahan
            if (!empty($changes)) {
                $this->sendCategoryUpdatedNotifications($category, $changes);
            }

            DB::commit();

            return redirect()->route('categories.index')
                ->with('success', 'Kategori berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating category: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal memperbarui kategori: ' . $e->getMessage());
        }
    }

    /**
     * Send notifications when category is updated
     */
    private function sendCategoryUpdatedNotifications($category, $changes)
    {
        try {
            $currentUser = auth()->user();
            
            // 1. Kirim ke semua user dengan role owner
            $owners = User::where('role', 'owner')
                ->where('id', '!=', $currentUser->id)
                ->get();
            
            foreach ($owners as $owner) {
                $owner->notify(new CategoryUpdatedNotification($category, $currentUser, $changes));
                Log::info('Notifikasi update kategori terkirim ke owner:', [
                    'owner_id' => $owner->id,
                    'category_id' => $category->id
                ]);
            }
            
            // 2. Kirim ke diri sendiri (pembuat update)
            $currentUser->notify(new CategoryUpdatedNotification($category, $currentUser, $changes));
            Log::info('Notifikasi update kategori terkirim ke diri sendiri:', [
                'user_id' => $currentUser->id,
                'category_id' => $category->id
            ]);
            
        } catch (\Exception $e) {
            Log::error('Gagal mengirim notifikasi update kategori: ' . $e->getMessage());
        }
    }

    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0) {
            return redirect()->route('categories.index')
                ->with('error', 'Tidak dapat menghapus kategori karena masih memiliki produk');
        }

        try {
            DB::beginTransaction();

            $categoryName = $category->name;
            $currentUser = auth()->user();
            
            $category->delete();

            // Kirim notifikasi penghapusan
            $this->sendCategoryDeletedNotifications($categoryName, $currentUser);

            DB::commit();

            return redirect()->route('categories.index')
                ->with('success', 'Kategori berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting category: ' . $e->getMessage());
            return redirect()->route('categories.index')
                ->with('error', 'Gagal menghapus kategori: ' . $e->getMessage());
        }
    }

    /**
     * Send notifications when category is deleted
     */
    private function sendCategoryDeletedNotifications($categoryName, $deletedBy)
    {
        try {
            // 1. Kirim ke semua user dengan role owner
            $owners = User::where('role', 'owner')
                ->where('id', '!=', $deletedBy->id)
                ->get();
            
            foreach ($owners as $owner) {
                $owner->notify(new CategoryDeletedNotification($categoryName, $deletedBy));
                Log::info('Notifikasi hapus kategori terkirim ke owner:', [
                    'owner_id' => $owner->id,
                    'category_name' => $categoryName
                ]);
            }
            
            // 2. Kirim ke diri sendiri (penghapus)
            $deletedBy->notify(new CategoryDeletedNotification($categoryName, $deletedBy));
            Log::info('Notifikasi hapus kategori terkirim ke diri sendiri:', [
                'user_id' => $deletedBy->id,
                'category_name' => $categoryName
            ]);
            
        } catch (\Exception $e) {
            Log::error('Gagal mengirim notifikasi hapus kategori: ' . $e->getMessage());
        }
    }
}