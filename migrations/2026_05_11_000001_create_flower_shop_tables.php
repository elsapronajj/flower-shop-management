<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable()->unique();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable()->unique();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        Schema::create('flowers', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('category_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->string('color')->nullable();
            $table->string('image_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->dateTime('order_date');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('flower_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });

        Schema::create('refresh_tokens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('token_hash', 128)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('revoked_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refresh_tokens');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('flowers');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('categories');
    }
};
