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
        Schema::create('agenda', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('cliente_id')->notNull();
            $table->uuid('lead_id');
            $table->date('data');
            $table->text('horario');
            $table->text('status');
            $table->text('observacoes');
            $table->timestamp('criado_em')->default(now());
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agenda');
    }
};
