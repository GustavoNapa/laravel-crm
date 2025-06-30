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
        Schema::create('mensagens_followup', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('cliente_id')->notNull();
            $table->uuid('lead_id');
            $table->text('mensagem');
            $table->text('tipo'); // 'texto' ou 'audio'
            $table->timestamp('agendado_para');
            $table->boolean('enviado')->default(false);
            $table->timestamp('criado_em')->default(now());
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mensagens_followup');
    }
};
