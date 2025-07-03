<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Verificar se a coluna já existe
        if (Schema::hasColumn('persons', 'unique_id')) {
            return;
        }

        Schema::table('persons', function (Blueprint $table) {
            $table->string('unique_id')->nullable()->unique();
        });

        $tableName = DB::getTablePrefix().'persons';

        // Usar sintaxe compatível com SQLite
        if (config('database.default') === 'sqlite') {
            DB::statement("
                UPDATE {$tableName}
                SET unique_id = 
                    COALESCE(user_id, 0) || '|' || 
                    COALESCE(organization_id, 0) || '|' || 
                    COALESCE(json_extract(emails, '$[0].value'), '') || '|' ||
                    COALESCE(json_extract(contact_numbers, '$[0].value'), '')
            ");
        } else {
            DB::statement("
                UPDATE {$tableName}
                SET unique_id = CONCAT(
                    user_id, '|', 
                    organization_id, '|', 
                    JSON_UNQUOTE(JSON_EXTRACT(emails, '$[0].value')), '|',
                    JSON_UNQUOTE(JSON_EXTRACT(contact_numbers, '$[0].value'))
                )
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            if (Schema::hasColumn('persons', 'unique_id')) {
                $table->dropColumn('unique_id');
            }
        });
    }
};
