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
        Schema::create('configuracoes_cliente', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('cliente_id')->unique();
            $table->text('prompt_qualificacao');
            $table->text('prompt_followup_1');
            $table->text('prompt_followup_2');
            $table->text('prompt_followup_3');
            $table->text('prompt_agendamento');
            $table->text('mensagem_boas_vindas');
            $table->text('mensagem_encerramento');
            $table->timestamp('criado_em')->default(now());
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracoes_cliente');
    }
};
