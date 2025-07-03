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

        // Verificar se as colunas já existem e se a migração é necessária
        $hasCode = Schema::hasColumn('lead_pipeline_stages', 'code');
        $hasName = Schema::hasColumn('lead_pipeline_stages', 'name');
        $hasLeadStageId = Schema::hasColumn('lead_pipeline_stages', 'lead_stage_id');

        // Se as colunas code e name já existem e lead_stage_id não existe,
        // a migração já foi executada
        if ($hasCode && $hasName && ! $hasLeadStageId) {
            return;
        }

        // Adicionar colunas se não existirem
        Schema::table('lead_pipeline_stages', function (Blueprint $table) use ($hasCode, $hasName) {
            if (! $hasCode) {
                $table->string('code')->after('id')->nullable();
            }
            if (! $hasName) {
                $table->string('name')->after('code')->nullable();
            }
        });

        // Migrar dados se necessário
        if ($hasLeadStageId && Schema::hasTable('lead_stages')) {
            try {
                // Verificar se lead_stages tem as colunas necessárias
                if (Schema::hasColumn('lead_stages', 'code') && Schema::hasColumn('lead_stages', 'name')) {
                    DB::statement('
                        UPDATE lead_pipeline_stages 
                        SET code = (SELECT code FROM lead_stages WHERE id = lead_pipeline_stages.lead_stage_id),
                            name = (SELECT name FROM lead_stages WHERE id = lead_pipeline_stages.lead_stage_id)
                        WHERE lead_stage_id IS NOT NULL
                    ');
                }
            } catch (\Exception $e) {
                // Se falhar, continuar sem migrar os dados
            }
        }

        // Remover coluna antiga (SQLite não suporta drop foreign key)
        if ($hasLeadStageId) {
            Schema::table('lead_pipeline_stages', function (Blueprint $table) {
                $table->dropColumn('lead_stage_id');
            });
        }

        // Adicionar índices únicos se não existirem
        try {
            Schema::table('lead_pipeline_stages', function (Blueprint $table) {
                $table->unique(['code', 'lead_pipeline_id'], 'lps_code_pipeline_unique');
                $table->unique(['name', 'lead_pipeline_id'], 'lps_name_pipeline_unique');
            });
        } catch (\Exception $e) {
            // Índices podem já existir
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lead_pipeline_stages', function (Blueprint $table) {
            try {
                $table->dropUnique('lps_code_pipeline_unique');
                $table->dropUnique('lps_name_pipeline_unique');
            } catch (\Exception $e) {
                // Índices podem não existir
            }

            if (Schema::hasColumn('lead_pipeline_stages', 'code')) {
                $table->dropColumn('code');
            }
            if (Schema::hasColumn('lead_pipeline_stages', 'name')) {
                $table->dropColumn('name');
            }

            if (! Schema::hasColumn('lead_pipeline_stages', 'lead_stage_id')) {
                $table->integer('lead_stage_id')->unsigned();
                $table->foreign('lead_stage_id')->references('id')->on('lead_stages')->onDelete('cascade');
            }
        });
    }
};
