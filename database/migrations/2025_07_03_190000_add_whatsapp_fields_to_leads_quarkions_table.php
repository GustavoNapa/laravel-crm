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
        Schema::table('leads_quarkions', function (Blueprint $table) {
            // Campos para foto de perfil
            $table->text('profile_photo')->nullable()->after('telefone');
            $table->text('whatsapp_wuid')->nullable()->after('profile_photo');
            $table->timestamp('profile_photo_sync_attempted')->nullable()->after('whatsapp_wuid');
            $table->text('profile_photo_sync_error')->nullable()->after('profile_photo_sync_attempted');

            // Campos para última mensagem
            $table->text('last_message')->nullable()->after('profile_photo_sync_error');
            $table->timestamp('last_message_timestamp')->nullable()->after('last_message');
            $table->boolean('last_message_from_me')->default(false)->after('last_message_timestamp');

            // Campo para contagem de mensagens não lidas
            $table->integer('unread_count')->default(0)->after('last_message_from_me');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads_quarkions', function (Blueprint $table) {
            $table->dropColumn([
                'profile_photo',
                'whatsapp_wuid',
                'profile_photo_sync_attempted',
                'profile_photo_sync_error',
                'last_message',
                'last_message_timestamp',
                'last_message_from_me',
                'unread_count',
            ]);
        });
    }
};
