<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        // SQLite não suporta alterar foreign keys
        // Esta migração é apenas para alterar o comportamento onDelete
        // No SQLite, vamos apenas pular esta alteração
        if (config('database.default') !== 'sqlite') {
            Schema::table('leads', function (Blueprint $table) {
                $table->dropForeign(['lead_pipeline_stage_id']);
                $table->foreign('lead_pipeline_stage_id')->references('id')->on('lead_pipeline_stages')->onDelete('set null');
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
        if (config('database.default') !== 'sqlite') {
            Schema::table('leads', function (Blueprint $table) {
                $table->dropForeign(['lead_pipeline_stage_id']);
                $table->foreign('lead_pipeline_stage_id')->references('id')->on('lead_pipeline_stages')->onDelete('cascade');
            });
        }
    }
};

