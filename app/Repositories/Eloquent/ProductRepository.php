<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ProductRepository extends BaseEloquentRepository implements ProductRepositoryInterface
{
    /**
     * ProductRepository constructor.
     *
     * @param Product $product
     */
    public function __construct(Product $product)
    {
        parent::__construct($product);
    }

    /**
     * @inheritDoc
     */
    public function getLowStock(int $threshold = 10)
    {
        return $this->model->where('stock', '<=', $threshold)
            ->where('stock', '>', 0)
            ->where('is_active', true)
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function getStatistics()
    {
        return [
            'total_products' => $this->model->count(),
            'active_products' => $this->model->where('is_active', true)->count(),
            'low_stock' => $this->getLowStock()->count(),
            'out_of_stock' => $this->model->where('stock', '<=', 0)->count(),
            'total_value' => (float) $this->model->sum(DB::raw('price * stock')),
        ];
    }

    /**
     * @inheritDoc
     */
    public function search(string $query, int $limit = 20)
    {
        return $this->model->where('name', 'LIKE', "%{$query}%")
            ->orWhere('code', 'LIKE', "%{$query}%")
            ->limit($limit)
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function updateStock($id, float $adjustment, string $type)
    {
        $product = $this->findById($id);
        if (!$product) return false;

        if ($type === 'add') {
            $product->stock += $adjustment;
        } elseif ($type === 'subtract') {
            $product->stock -= $adjustment;
        } else {
            $product->stock = $adjustment;
        }

        return $product->save();
    }

    /**
     * @inheritDoc
     */
    public function toggleStatus($id)
    {
        $product = $this->findById($id);
        if (!$product) return false;

        $product->is_active = !$product->is_active;
        return $product->save();
    }
}
