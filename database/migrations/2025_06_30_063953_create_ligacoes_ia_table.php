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
        Schema::create('ligacoes_ia', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('cliente_id')->notNull();
            $table->uuid('lead_id');
            $table->text('status');
            $table->text('duracao');
            $table->text('transcricao');
            $table->timestamp('criado_em')->default(now());
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ligacoes_ia');
    }
};
