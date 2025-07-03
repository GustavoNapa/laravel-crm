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
        // Vamos apenas alterar as colunas para nullable
        Schema::table('leads', function (Blueprint $table) {
            $table->integer('user_id')->unsigned()->nullable()->change();
            $table->integer('person_id')->unsigned()->nullable()->change();
            $table->integer('lead_source_id')->unsigned()->nullable()->change();
            $table->integer('lead_type_id')->unsigned()->nullable()->change();
        });

        // Para outros bancos que suportam alterar foreign keys
        if (config('database.default') !== 'sqlite') {
            Schema::table('leads', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropForeign(['person_id']);
                $table->dropForeign(['lead_source_id']);
                $table->dropForeign(['lead_type_id']);

                $table->foreign('user_id')
                    ->references('id')->on('users')
                    ->onDelete('set null');

                $table->foreign('person_id')
                    ->references('id')->on('persons')
                    ->onDelete('restrict');

                $table->foreign('lead_source_id')
                    ->references('id')->on('lead_sources')
                    ->onDelete('restrict');

                $table->foreign('lead_type_id')
                    ->references('id')->on('lead_types')
                    ->onDelete('restrict');
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
                $table->dropForeign(['user_id']);
                $table->dropForeign(['person_id']);
                $table->dropForeign(['lead_source_id']);
                $table->dropForeign(['lead_type_id']);

                $table->foreign('user_id')
                    ->references('id')->on('users')
                    ->onDelete('cascade');

                $table->foreign('person_id')
                    ->references('id')->on('persons')
                    ->onDelete('cascade');

                $table->foreign('lead_source_id')
                    ->references('id')->on('lead_sources')
                    ->onDelete('cascade');

                $table->foreign('lead_type_id')
                    ->references('id')->on('lead_types')
                    ->onDelete('cascade');
            });
        }

        Schema::table('leads', function (Blueprint $table) {
            $table->integer('user_id')->unsigned()->nullable()->change();
            $table->integer('person_id')->unsigned()->nullable(false)->change();
            $table->integer('lead_source_id')->unsigned()->nullable(false)->change();
            $table->integer('lead_type_id')->unsigned()->nullable(false)->change();
        });
    }
};

