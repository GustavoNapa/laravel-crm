<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // SQLite não suporta alterar foreign keys
        // Esta migração é apenas para alterar o comportamento onDelete
        if (config('database.default') !== 'sqlite') {
            Schema::table('product_inventories', function (Blueprint $table) {
                $table->dropForeign(['warehouse_location_id']);
                $table->foreign('warehouse_location_id')->references('id')->on('warehouse_locations')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        if (config('database.default') !== 'sqlite') {
            Schema::table('product_inventories', function (Blueprint $table) {
                $table->dropForeign(['warehouse_location_id']);
                $table->foreign('warehouse_location_id')->references('id')->on('warehouse_locations')->onDelete('set null');
            });
        }
    }
};
