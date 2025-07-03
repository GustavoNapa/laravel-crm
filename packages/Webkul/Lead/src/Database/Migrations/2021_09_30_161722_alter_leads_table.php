<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tablePrefix = DB::getTablePrefix();

        // Verificar se a migração já foi executada
        $hasLeadPipelineStageId = Schema::hasColumn('leads', 'lead_pipeline_stage_id');
        $hasLeadStageId = Schema::hasColumn('leads', 'lead_stage_id');

        // Se lead_pipeline_stage_id existe e lead_stage_id não existe, migração já foi executada
        if ($hasLeadPipelineStageId && ! $hasLeadStageId) {
            return;
        }

        // Adicionar nova coluna se não existir
        if (! $hasLeadPipelineStageId) {
            Schema::table('leads', function (Blueprint $table) {
                $table->integer('lead_pipeline_stage_id')->after('lead_pipeline_id')->unsigned()->nullable();
                $table->foreign('lead_pipeline_stage_id')->references('id')->on('lead_pipeline_stages')->onDelete('cascade');
            });
        }

        // Migrar dados se necessário
        if ($hasLeadStageId) {
            try {
                DB::statement('
                    UPDATE leads 
                    SET lead_pipeline_stage_id = lead_stage_id 
                    WHERE lead_stage_id IS NOT NULL
                ');
            } catch (\Exception $e) {
                // Se falhar, continuar sem migrar os dados
            }
        }

        // Remover coluna antiga (SQLite não suporta drop foreign key)
        if ($hasLeadStageId) {
            Schema::table('leads', function (Blueprint $table) {
                $table->dropColumn('lead_stage_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            if (Schema::hasColumn('leads', 'lead_pipeline_stage_id')) {
                $table->dropColumn('lead_pipeline_stage_id');
            }

            if (! Schema::hasColumn('leads', 'lead_stage_id')) {
                $table->integer('lead_stage_id')->unsigned();
                $table->foreign('lead_stage_id')->references('id')->on('lead_stages')->onDelete('cascade');
            }
        });
    }
};
