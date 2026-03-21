<?php

namespace App\Services;

use App\Repositories\Contracts\ProductRepositoryInterface;
use Exception;

class ProductService extends BaseService
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * ProductService constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Get all products with optional filters.
     *
     * @param array $filters
     * @return mixed
     */
    public function getAllProducts(array $filters = [])
    {
        try {
            return $this->productRepository->paginate($filters['per_page'] ?? 15);
        } catch (Exception $e) {
            $this->handleException($e, 'Error fetching products');
            throw $e;
        }
    }

    /**
     * Get product by ID.
     */
    public function getProductById($id)
    {
        try {
            return $this->productRepository->findById($id);
        } catch (Exception $e) {
            $this->handleException($e, "Error fetching product with ID {$id}");
            throw $e;
        }
    }

    /**
     * Create a new product.
     */
    public function createProduct(array $data)
    {
        try {
            return $this->productRepository->create($data);
        } catch (Exception $e) {
            $this->handleException($e, 'Error creating product');
            throw $e;
        }
    }

    /**
     * Update an existing product.
     */
    public function updateProduct($id, array $data)
    {
        try {
            return $this->productRepository->update($id, $data);
        } catch (Exception $e) {
            $this->handleException($e, "Error updating product with ID {$id}");
            throw $e;
        }
    }

    /**
     * Delete a product.
     */
    public function deleteProduct($id)
    {
        try {
            return $this->productRepository->delete($id);
        } catch (Exception $e) {
            $this->handleException($e, "Error deleting product with ID {$id}");
            throw $e;
        }
    }

    /**
     * Search products.
     */
    public function searchProducts(string $query)
    {
        try {
            return $this->productRepository->search($query);
        } catch (Exception $e) {
            $this->handleException($e, 'Error searching products');
            throw $e;
        }
    }

    /**
     * Get product statistics.
     */
    public function getProductStats()
    {
        try {
            return $this->productRepository->getStatistics();
        } catch (Exception $e) {
            $this->handleException($e, 'Error fetching product statistics');
            throw $e;
        }
    }

    /**
     * Get low stock products.
     */
    public function getLowStockProducts()
    {
        try {
            return $this->productRepository->getLowStock();
        } catch (Exception $e) {
            $this->handleException($e, 'Error fetching low stock products');
            throw $e;
        }
    }

    /**
     * Update product stock.
     */
    public function updateStock($id, float $adjustment, string $type)
    {
        try {
            return $this->productRepository->updateStock($id, $adjustment, $type);
        } catch (Exception $e) {
            $this->handleException($e, 'Error updating stock');
            throw $e;
        }
    }

    /**
     * Toggle product active status.
     */
    public function toggleProductStatus($id)
    {
        try {
            return $this->productRepository->toggleStatus($id);
        } catch (Exception $e) {
            $this->handleException($e, 'Error toggling product status');
            throw $e;
        }
    }
}
