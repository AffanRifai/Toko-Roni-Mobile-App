<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Notifications\CategoryCreatedNotification;
use App\Notifications\CategoryDeletedNotification;
use App\Notifications\CategoryUpdatedNotification;
use App\Services\NotificationRoutingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryApiController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // Middleware handled in routes/api.php
    }

    /**
     * Display a listing of categories.
     */
    public function index(Request $request)
    {
        try {
            $query = Category::query();

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%");
            }

            if ($request->filled('is_active')) {
                $query->where('is_active', $request->is_active === 'true' || $request->is_active == 1);
            }

            $categories = $query->withCount('products')->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'message' => 'Categories retrieved successfully',
                'data' => $categories
            ], 200);

        } catch (\Exception $e) {
            Log::error('API Category index error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $payload = $this->buildCategoryPayload($request);
        $validated = Validator::make($payload, [
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'slug' => ['required', 'string', 'max:255', 'unique:categories,slug'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ])->validate();

        try {
            $category = Category::create($validated);
            $actor = auth()->user();
            if ($actor instanceof User) {
                $this->notifyByEvent(
                    eventKey: 'category.created',
                    notification: new CategoryCreatedNotification($category, $actor),
                    actor: $actor
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => $category
            ], 201);

        } catch (\Exception $e) {
            Log::error('API Category store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified category.
     */
    public function show($id)
    {
        try {
            $category = Category::withCount('products')->find($id);

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Category retrieved successfully',
                'data' => $category
            ], 200);

        } catch (\Exception $e) {
            Log::error('API Category show error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $payload = $this->buildCategoryPayload($request, $category);
        $validated = Validator::make($payload, [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($id),
            ],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'slug')->ignore($id),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ])->validate();

        try {
            $before = $category->getOriginal();
            $category->update($validated);
            $actor = auth()->user();
            if ($actor instanceof User) {
                $changes = [];
                foreach ($validated as $key => $value) {
                    $oldValue = $before[$key] ?? null;
                    if ((string) $oldValue !== (string) $value) {
                        $changes[$key] = $value;
                    }
                }

                if (!empty($changes)) {
                    $this->notifyByEvent(
                        eventKey: 'category.updated',
                        notification: new CategoryUpdatedNotification($category->fresh(), $actor, $changes),
                        actor: $actor
                    );
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'data' => $category->fresh()
            ], 200);

        } catch (\Exception $e) {
            Log::error('API Category update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified category.
     */
    public function destroy($id)
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }

            // Check if category has products
            if ($category->products()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete category that has products'
                ], 400);
            }

            $categoryName = $category->name;
            $actor = auth()->user();
            $category->delete();
            if ($actor instanceof User) {
                $this->notifyByEvent(
                    eventKey: 'category.deleted',
                    notification: new CategoryDeletedNotification($categoryName, $actor),
                    actor: $actor
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('API Category delete error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get products in a category.
     */
    public function products(Request $request, $id)
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }

            $query = $category->products()->with('category')->latest();
            
            $perPage = $request->get('per_page', 20);
            $products = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Products in category retrieved successfully',
                'data' => $products
            ], 200);

        } catch (\Exception $e) {
            Log::error('API Category products error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve products in category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function notifyByEvent(string $eventKey, $notification, User $actor): void
    {
        try {
            /** @var NotificationRoutingService $router */
            $router = app(NotificationRoutingService::class);
            $router->send($eventKey, $notification, $actor);
        } catch (\Throwable $e) {
            Log::warning('Category API notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Normalize multi-alias payload from mobile/web into canonical category fields.
     */
    private function buildCategoryPayload(Request $request, ?Category $existing = null): array
    {
        $nameRaw = $this->firstFilled($request, [
            'name',
            'nama',
            'nama_kategori',
            'category_name',
        ]);
        $name = $nameRaw !== null
            ? trim((string) $nameRaw)
            : ($existing?->name ?? '');

        $slugRaw = $this->firstFilled($request, [
            'slug',
            'slug_url',
            'slug_kategori',
            'category_slug',
        ]);
        $slugSource = $slugRaw !== null ? (string) $slugRaw : $name;
        $slug = Str::slug($slugSource);
        if ($slug === '' && $existing !== null) {
            $slug = (string) $existing->slug;
        }

        $descriptionRaw = $this->firstPresent($request, [
            'description',
            'deskripsi',
            'desc',
        ]);
        $description = $descriptionRaw !== null ? trim((string) $descriptionRaw) : null;
        if ($description === '') {
            $description = null;
        }
        if ($descriptionRaw === null && $existing !== null) {
            $description = $existing->description;
        }

        $isActiveRaw = $this->firstPresent($request, ['is_active', 'aktif', 'status']);
        $isActive = $existing?->is_active ?? true;
        if ($isActiveRaw !== null) {
            $isActive = $this->toBool($isActiveRaw, $isActive);
        }

        return [
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'is_active' => $isActive,
        ];
    }

    private function firstFilled(Request $request, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (!$request->exists($key)) {
                continue;
            }
            $value = $request->input($key);
            if ($value === null) {
                continue;
            }
            if (is_string($value) && trim($value) === '') {
                continue;
            }
            return $value;
        }
        return null;
    }

    private function firstPresent(Request $request, array $keys): mixed
    {
        foreach ($keys as $key) {
            if ($request->exists($key)) {
                return $request->input($key);
            }
        }
        return null;
    }

    private function toBool(mixed $value, bool $default): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_int($value)) {
            return $value === 1;
        }
        if (is_string($value)) {
            $normalized = strtolower(trim($value));
            if (in_array($normalized, ['1', 'true', 'aktif', 'active', 'yes'], true)) {
                return true;
            }
            if (in_array($normalized, ['0', 'false', 'nonaktif', 'inactive', 'no'], true)) {
                return false;
            }
        }
        return $default;
    }
}
