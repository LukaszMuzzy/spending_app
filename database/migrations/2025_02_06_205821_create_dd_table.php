<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dd_payments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->dateTime('payment_date');
            $table->dateTime('end_date')->nullable();
            $table->enum('frequency', ['daily', 'weekly', 'fortnightly', 'monthly', 'quarterly', 'half-yearly', 'yearly'])->nullable();
            $table->float('price', 8, 2);
            $table->foreignId('shopping_group_id')->constrained();
            $table->foreignId('shop_id')->constrained();
            $table->foreignId('payment_method_id')->constrained();
            $table->foreignId('shopping_type_id')->constrained();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dd_payments');
    }
};
