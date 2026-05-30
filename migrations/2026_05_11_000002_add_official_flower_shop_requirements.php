<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('flowers', function (Blueprint $table): void {
            $table->string('type')->nullable()->after('name');
            $table->string('season')->nullable()->after('stock_quantity');
            $table->unsignedInteger('lifespan_days')->nullable()->after('season');
            $table->string('photo')->nullable()->after('image_url');
        });

        Schema::table('customers', function (Blueprint $table): void {
            $table->date('registration_date')->nullable()->after('address');
            $table->boolean('is_vip')->default(false)->after('registration_date');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->date('delivery_date')->nullable()->after('order_date');
            $table->text('delivery_address')->nullable()->after('delivery_date');
            $table->text('card_message')->nullable()->after('delivery_address');
        });

        Schema::table('suppliers', function (Blueprint $table): void {
            $table->string('contact')->nullable()->after('name');
            $table->string('specialty')->nullable()->after('address');
        });

        Schema::create('bouquets', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('size')->nullable();
            $table->string('photo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('bouquet_flowers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('bouquet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('flower_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamps();
            $table->unique(['bouquet_id', 'flower_id']);
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->foreignId('flower_id')->nullable()->change();
            $table->foreignId('bouquet_id')->nullable()->after('order_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->decimal('amount', 10, 2)->nullable()->after('subtotal');
        });

        DB::table('order_items')->whereNull('amount')->update([
            'amount' => DB::raw('subtotal'),
        ]);

        Schema::create('deliveries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('courier_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->date('delivery_date');
            $table->time('delivery_time')->nullable();
            $table->enum('status', ['pending', 'in_transit', 'delivered', 'failed'])->default('pending');
            $table->text('recipient_signature')->nullable();
            $table->timestamps();
        });

        Schema::create('occasions', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('event_date')->nullable();
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('supply_orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->date('order_date');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->enum('status', ['pending', 'ordered', 'received', 'cancelled'])->default('pending');
            $table->timestamps();
        });

        Schema::create('reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->date('review_date');
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('user_roles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('supply_orders');
        Schema::dropIfExists('occasions');
        Schema::dropIfExists('deliveries');

        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('bouquet_id');
            $table->dropColumn('amount');
        });

        Schema::dropIfExists('bouquet_flowers');
        Schema::dropIfExists('bouquets');

        Schema::table('suppliers', function (Blueprint $table): void {
            $table->dropColumn(['contact', 'specialty']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['delivery_date', 'delivery_address', 'card_message']);
        });

        Schema::table('customers', function (Blueprint $table): void {
            $table->dropColumn(['registration_date', 'is_vip']);
        });

        Schema::table('flowers', function (Blueprint $table): void {
            $table->dropColumn(['type', 'season', 'lifespan_days', 'photo']);
        });
    }
};
