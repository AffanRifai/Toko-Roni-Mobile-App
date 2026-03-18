<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'owner']);
    }

    /**
     * Test list products.
     */
    public function test_can_list_products()
    {
        Product::factory()->count(5)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total'
                ]
            ]);
    }

    /**
     * Test search products.
     */
    public function test_can_search_products()
    {
        Product::factory()->create(['name' => 'Kopi Kapal Api']);
        Product::factory()->create(['name' => 'Teh Botol']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/products/search?query=Kopi');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Kopi Kapal Api']);
    }

    /**
     * Test get product stats.
     */
    public function test_can_get_product_statistics()
    {
        Product::factory()->count(10)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/products/statistics/overview');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'total_products',
                    'active_products',
                    'low_stock',
                    'out_of_stock',
                    'total_value'
                ]
            ]);
    }

    /**
     * Test update stock.
     */
    public function test_can_update_product_stock()
    {
        $product = Product::factory()->create(['stock' => 10]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/products/{$product->id}/update-stock", [
                'adjustment' => 5,
                'type' => 'add'
            ]);

        $response->assertStatus(200);
        $this->assertEquals(15, $product->fresh()->stock);
    }

    /**
     * Test unauthorized access.
     */
    public function test_unauthorized_access_fails()
    {
        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated',
            ]);
    }
}
