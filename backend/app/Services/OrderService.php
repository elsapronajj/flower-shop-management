<?php

namespace App\Services;

use App\Models\Flower;
use App\Models\Order;
use App\Models\Bouquet;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function create(array $data): Order
    {
        return DB::transaction(function () use ($data): Order {
            $status = $data['status'] ?? Order::STATUS_PENDING;
            $items = $data['items'];
            $preparedItems = $this->prepareItems($items, $status !== Order::STATUS_CANCELLED);

            $order = Order::create([
                'customer_id' => $data['customer_id'],
                'order_date' => $data['order_date'] ?? now(),
                'delivery_date' => $data['delivery_date'] ?? null,
                'delivery_address' => $data['delivery_address'] ?? null,
                'card_message' => $data['card_message'] ?? null,
                'status' => $status,
                'total_amount' => $this->calculateTotal($preparedItems),
            ]);

            $order->items()->createMany($preparedItems);

            return $order->load(['customer', 'items.flower.category', 'items.flower.supplier', 'items.bouquet.bouquetFlowers.flower']);
        });
    }

    public function update(Order $order, array $data): Order
    {
        return DB::transaction(function () use ($order, $data): Order {
            $order->load('items');
            $oldStatus = $order->status;
            $newStatus = $data['status'] ?? $order->status;
            $hasNewItems = array_key_exists('items', $data);

            if ($hasNewItems && $oldStatus !== Order::STATUS_CANCELLED) {
                $this->restoreStock($order);
            }

            if (! $hasNewItems && $oldStatus !== Order::STATUS_CANCELLED && $newStatus === Order::STATUS_CANCELLED) {
                $this->restoreStock($order);
            }

            if ($hasNewItems) {
                $order->items()->delete();
                $preparedItems = $this->prepareItems($data['items'], $newStatus !== Order::STATUS_CANCELLED);
                $order->items()->createMany($preparedItems);
                $data['total_amount'] = $this->calculateTotal($preparedItems);
            } elseif ($oldStatus === Order::STATUS_CANCELLED && $newStatus !== Order::STATUS_CANCELLED) {
                $this->reduceExistingOrderStock($order);
            }

            unset($data['items']);

            $order->update([
                ...$data,
                'status' => $newStatus,
                'total_amount' => $data['total_amount'] ?? $order->items()->sum('subtotal'),
            ]);

            return $order->refresh()->load(['customer', 'items.flower.category', 'items.flower.supplier', 'items.bouquet.bouquetFlowers.flower']);
        });
    }

    public function delete(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $order->load('items');

            if ($order->status !== Order::STATUS_CANCELLED) {
                $this->restoreStock($order);
            }

            $order->delete();
        });
    }

    private function prepareItems(array $items, bool $reduceStock): array
    {
        $quantities = [];
        $sourceItems = [];

        foreach ($items as $item) {
            $hasFlower = ! empty($item['flower_id']);
            $hasBouquet = ! empty($item['bouquet_id']);

            if ($hasFlower === $hasBouquet) {
                throw ValidationException::withMessages([
                    'items' => 'Each order item must contain either a flower or a bouquet, but not both.',
                ]);
            }

            $key = $hasBouquet ? 'bouquet:'.$item['bouquet_id'] : 'flower:'.$item['flower_id'];
            $quantities[$key] = ($quantities[$key] ?? 0) + (int) $item['quantity'];
            $sourceItems[$key] = $item;
        }

        $flowers = Flower::query()
            ->whereIn('id', collect(array_keys($sourceItems))
                ->filter(fn (string $key) => str_starts_with($key, 'flower:'))
                ->map(fn (string $key) => (int) substr($key, 7))
                ->all())
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        $bouquets = Bouquet::query()
            ->with('bouquetFlowers.flower')
            ->whereIn('id', collect(array_keys($sourceItems))
                ->filter(fn (string $key) => str_starts_with($key, 'bouquet:'))
                ->map(fn (string $key) => (int) substr($key, 8))
                ->all())
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        $prepared = [];

        foreach ($quantities as $key => $quantity) {
            if (str_starts_with($key, 'flower:')) {
                $prepared[] = $this->prepareFlowerItem((int) substr($key, 7), $quantity, $flowers, $reduceStock);
                continue;
            }

            $prepared[] = $this->prepareBouquetItem((int) substr($key, 8), $quantity, $bouquets, $reduceStock);
        }

        return $prepared;
    }

    private function prepareFlowerItem(int $flowerId, int $quantity, $flowers, bool $reduceStock): array
    {
        $flower = $flowers->get($flowerId);

        if (! $flower || ! $flower->is_active) {
            throw ValidationException::withMessages([
                'items' => "Flower {$flowerId} is not available.",
            ]);
        }

        if ($quantity > $flower->stock_quantity) {
            throw ValidationException::withMessages([
                'items' => "Requested quantity for {$flower->name} exceeds available stock. Available: {$flower->stock_quantity}.",
            ]);
        }

        $unitPrice = (float) $flower->price;
        $subtotal = $unitPrice * $quantity;

        if ($reduceStock) {
            $flower->decrement('stock_quantity', $quantity);
        }

        return [
            'flower_id' => $flower->id,
            'bouquet_id' => null,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $subtotal,
            'amount' => $subtotal,
        ];
    }

    private function prepareBouquetItem(int $bouquetId, int $quantity, $bouquets, bool $reduceStock): array
    {
        $bouquet = $bouquets->get($bouquetId);

        if (! $bouquet || ! $bouquet->is_active) {
            throw ValidationException::withMessages([
                'items' => "Bouquet {$bouquetId} is not available.",
            ]);
        }

        if ($bouquet->bouquetFlowers->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => "{$bouquet->name} has no flower composition configured.",
            ]);
        }

        foreach ($bouquet->bouquetFlowers as $bouquetFlower) {
            $needed = $bouquetFlower->quantity * $quantity;
            $flower = Flower::whereKey($bouquetFlower->flower_id)->lockForUpdate()->first();

            if (! $flower || ! $flower->is_active || $needed > $flower->stock_quantity) {
                throw ValidationException::withMessages([
                    'items' => "Requested quantity for {$bouquet->name} exceeds available stock for {$bouquetFlower->flower?->name}.",
                ]);
            }
        }

        foreach ($bouquet->bouquetFlowers as $bouquetFlower) {
            if ($reduceStock) {
                Flower::whereKey($bouquetFlower->flower_id)->decrement('stock_quantity', $bouquetFlower->quantity * $quantity);
            }
        }

        $unitPrice = (float) $bouquet->price;
        $subtotal = $unitPrice * $quantity;

        return [
            'flower_id' => null,
            'bouquet_id' => $bouquet->id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $subtotal,
            'amount' => $subtotal,
        ];
    }

    private function restoreStock(Order $order): void
    {
        foreach ($order->items as $item) {
            if ($item->flower_id) {
                Flower::whereKey($item->flower_id)->increment('stock_quantity', $item->quantity);
                continue;
            }

            if ($item->bouquet_id) {
                $item->loadMissing('bouquet.bouquetFlowers');

                foreach ($item->bouquet->bouquetFlowers as $bouquetFlower) {
                    Flower::whereKey($bouquetFlower->flower_id)->increment('stock_quantity', $bouquetFlower->quantity * $item->quantity);
                }
            }
        }
    }

    private function reduceExistingOrderStock(Order $order): void
    {
        foreach ($order->items as $item) {
            if ($item->bouquet_id) {
                $this->prepareBouquetItem($item->bouquet_id, $item->quantity, Bouquet::with('bouquetFlowers.flower')->whereKey($item->bouquet_id)->get()->keyBy('id'), true);
                continue;
            }

            $flower = Flower::whereKey($item->flower_id)->lockForUpdate()->first();

            if (! $flower || $item->quantity > $flower->stock_quantity) {
                $flowerName = $item->flower?->name ?? 'a flower';

                throw ValidationException::withMessages([
                    'items' => "Requested quantity for {$flowerName} exceeds available stock.",
                ]);
            }

            $flower->decrement('stock_quantity', $item->quantity);
        }
    }

    private function calculateTotal(array $items): float
    {
        return array_sum(array_column($items, 'subtotal'));
    }
}
