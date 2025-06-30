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
        Schema::table('agenda', function (Blueprint $table) {
            $table->string('google_event_id')->nullable()->after('observacoes');
            $table->timestamp('synced_at')->nullable()->after('google_event_id');
            $table->boolean('sync_with_google')->default(false)->after('synced_at');
            $table->json('google_metadata')->nullable()->after('sync_with_google');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agenda', function (Blueprint $table) {
            $table->dropColumn(['google_event_id', 'synced_at', 'sync_with_google', 'google_metadata']);
        });
    }
};
