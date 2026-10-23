<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider')->default('enable_banking');
            $table->string('institution_name')->nullable();
            $table->string('status')->default('pending');
            $table->text('session_id')->nullable();
            $table->json('accounts')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->decimal('balance', 12, 2)->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_connections');
    }
};
