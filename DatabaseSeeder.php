<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Bouquet;
use App\Models\BouquetFlower;
use App\Models\Customer;
use App\Models\Flower;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@flowershop.test'],
            ['name' => 'Flower Shop Admin', 'password' => Hash::make('password')]
        );

        $role = Role::firstOrCreate(['name' => 'Admin'], ['description' => 'Full system access']);
        $admin->roles()->syncWithoutDetaching([$role->id]);

        $categories = collect([
            ['name' => 'Roses', 'description' => 'Classic roses for bouquets and arrangements.'],
            ['name' => 'Tulips', 'description' => 'Seasonal tulips in bright colors.'],
            ['name' => 'Lilies', 'description' => 'Elegant lilies for premium orders.'],
        ])->map(fn (array $data) => Category::firstOrCreate(['name' => $data['name']], $data));

        $supplier = Supplier::firstOrCreate(
            ['email' => 'supply@petalpartners.test'],
            [
                'name' => 'Petal Partners',
                'phone' => '+1 555 0100',
                'address' => '14 Garden Lane',
            ]
        );

        Customer::firstOrCreate(
            ['email' => 'amelia@example.com'],
            [
                'first_name' => 'Amelia',
                'last_name' => 'Stone',
                'phone' => '+1 555 0199',
                'address' => '22 Blossom Street',
            ]
        );

        foreach ([
            ['Red Velvet Rose', 'Deep red long-stem rose.', 5.99, 32, 'Red', $categories[0]->id],
            ['Sunny Tulip', 'Bright yellow tulip bunch.', 3.49, 24, 'Yellow', $categories[1]->id],
            ['White Lily', 'Fresh white lily stem.', 6.75, 8, 'White', $categories[2]->id],
        ] as [$name, $description, $price, $stock, $color, $categoryId]) {
            Flower::firstOrCreate(
                ['name' => $name],
                [
                    'description' => $description,
                    'category_id' => $categoryId,
                    'supplier_id' => $supplier->id,
                    'price' => $price,
                    'stock_quantity' => $stock,
                    'color' => $color,
                    'is_active' => true,
                ]
            );
        }

        $bouquet = Bouquet::firstOrCreate(
            ['name' => 'Classic Garden Bouquet'],
            [
                'description' => 'Mixed bouquet for everyday gifts.',
                'price' => 29.99,
                'size' => 'Medium',
                'is_active' => true,
            ]
        );

        Flower::query()->limit(2)->get()->each(function (Flower $flower) use ($bouquet): void {
            BouquetFlower::updateOrCreate(
                ['bouquet_id' => $bouquet->id, 'flower_id' => $flower->id],
                ['quantity' => 2]
            );
        });
    }
}
