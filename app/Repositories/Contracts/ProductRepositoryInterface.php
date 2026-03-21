<?php

namespace App\Repositories\Contracts;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get low stock products.
     *
     * @param int $threshold
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLowStock(int $threshold = 10);

    /**
     * Get product statistics.
     *
     * @return array
     */
    public function getStatistics();

    /**
     * Search products.
     *
     * @param string $query
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function search(string $query, int $limit = 20);

    /**
     * Update product stock.
     *
     * @param int|string $id
     * @param float $adjustment
     * @param string $type
     * @return bool
     */
    public function updateStock($id, float $adjustment, string $type);

    /**
     * Toggle product status.
     *
     * @param int|string $id
     * @return bool
     */
    public function toggleStatus($id);
}
