<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Bouquet;
use App\Models\BouquetFlower;
use App\Models\Customer;
use App\Models\Flower;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FlowerShopApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_order_flow_reduces_and_restores_stock(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $loginResponse = $this->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $loginResponse->assertOk()->assertJsonPath('success', true);
        $token = $loginResponse->json('data.access_token');

        $category = Category::create(['name' => 'Roses']);
        $supplier = Supplier::create(['name' => 'Local Grower', 'email' => 'grower@example.com']);
        $customer = Customer::create([
            'first_name' => 'Mira',
            'last_name' => 'Bloom',
            'email' => 'mira@example.com',
        ]);
        $flower = Flower::create([
            'name' => 'Red Rose',
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'price' => 4.5,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        $orderResponse = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/orders', [
                'customer_id' => $customer->id,
                'status' => 'pending',
                'items' => [
                    ['flower_id' => $flower->id, 'quantity' => 3],
                ],
            ]);

        $orderResponse
            ->assertCreated()
            ->assertJsonPath('data.total_amount', 13.5)
            ->assertJsonPath('data.items.0.subtotal', 13.5);

        $this->assertSame(7, $flower->refresh()->stock_quantity);

        $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/orders/'.$orderResponse->json('data.id'), [
                'status' => 'cancelled',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');

        $this->assertSame(10, $flower->refresh()->stock_quantity);
    }

    public function test_order_quantity_cannot_exceed_available_stock(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $token = $this->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ])->json('data.access_token');

        $category = Category::create(['name' => 'Tulips']);
        $supplier = Supplier::create(['name' => 'Local Grower', 'email' => 'grower@example.com']);
        $customer = Customer::create([
            'first_name' => 'Mira',
            'last_name' => 'Bloom',
            'email' => 'mira@example.com',
        ]);
        $flower = Flower::create([
            'name' => 'Yellow Tulip',
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'price' => 3.25,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/orders', [
                'customer_id' => $customer->id,
                'status' => 'pending',
                'items' => [
                    ['flower_id' => $flower->id, 'quantity' => 11],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('items');

        $this->assertSame(10, $flower->refresh()->stock_quantity);
    }

    public function test_bouquet_order_reduces_and_restores_composed_flower_stock(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $token = $this->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ])->json('data.access_token');

        $category = Category::create(['name' => 'Bouquet Flowers']);
        $supplier = Supplier::create(['name' => 'Local Grower', 'email' => 'grower@example.com']);
        $customer = Customer::create([
            'first_name' => 'Mira',
            'last_name' => 'Bloom',
            'email' => 'mira@example.com',
        ]);
        $flower = Flower::create([
            'name' => 'Pink Rose',
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'price' => 4.5,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);
        $bouquet = Bouquet::create([
            'name' => 'Pink Gift Bouquet',
            'price' => 30,
            'is_active' => true,
        ]);
        BouquetFlower::create([
            'bouquet_id' => $bouquet->id,
            'flower_id' => $flower->id,
            'quantity' => 2,
        ]);

        $orderResponse = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/orders', [
                'customer_id' => $customer->id,
                'status' => 'pending',
                'items' => [
                    ['bouquet_id' => $bouquet->id, 'quantity' => 3],
                ],
            ]);

        $orderResponse
            ->assertCreated()
            ->assertJsonPath('data.total_amount', 90)
            ->assertJsonPath('data.items.0.amount', 90);

        $this->assertSame(4, $flower->refresh()->stock_quantity);

        $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/orders/'.$orderResponse->json('data.id'), [
                'status' => 'cancelled',
            ])
            ->assertOk();

        $this->assertSame(10, $flower->refresh()->stock_quantity);
    }
}
