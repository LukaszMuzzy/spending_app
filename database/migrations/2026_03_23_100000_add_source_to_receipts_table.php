<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->string('source')->default('manual')->after('note');
            $table->string('external_id')->nullable()->after('source');
            $table->boolean('needs_review')->default(false)->after('external_id');

            $table->unique(['source', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->dropUnique(['source', 'external_id']);
            $table->dropColumn(['source', 'external_id', 'needs_review']);
        });
    }
};
