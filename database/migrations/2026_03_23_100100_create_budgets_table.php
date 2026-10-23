<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shopping_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shopping_type_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->timestamps();

            $table->unique(['shopping_group_id', 'shopping_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
