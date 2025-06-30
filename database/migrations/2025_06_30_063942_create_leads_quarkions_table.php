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
        Schema::create('leads_quarkions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('nome')->notNull();
            $table->text('telefone');
            $table->text('email');
            $table->text('status');
            $table->text('origem');
            $table->text('cliente_id')->notNull();
            $table->timestamp('criado_em')->default(now());
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads_quarkions');
    }
};
