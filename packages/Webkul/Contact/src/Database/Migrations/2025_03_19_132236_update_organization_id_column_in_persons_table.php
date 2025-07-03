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
        // SQLite não suporta alterar foreign keys
        // Esta migração é apenas para alterar o comportamento onDelete
        if (config('database.default') !== 'sqlite') {
            Schema::table('persons', function (Blueprint $table) {
                $table->dropForeign(['organization_id']);
                $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (config('database.default') !== 'sqlite') {
            Schema::table('persons', function (Blueprint $table) {
                $table->dropForeign(['organization_id']);
                $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            });
        }
    }
};
