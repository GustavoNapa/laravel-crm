<?php

use Illuminate\Support\Facades\Route;
use Webkul\Admin\Http\Controllers\QuarkionsController;

/**
 * Simple test route
 */
Route::get('test-quarkions', function() {
    return response()->json([
        'status' => 'success',
        'message' => 'Quarkions routes estão funcionando',
        'timestamp' => now()
    ]);
})->name('admin.test.quarkions');

/**
 * Test WhatsApp controller
 */
Route::get('test-whatsapp-controller', [QuarkionsController::class, 'whatsappIndex'])->name('admin.test.whatsapp.controller');

/**
 * Simple WhatsApp test without Vue
 */
Route::get('test-whatsapp-simple', function() {
    return view('admin::quarkions.whatsapp.simple-test');
})->name('admin.test.whatsapp.simple');

/**
 * Quarkions routes.
 */
Route::group(['middleware' => ['user']], function () {
    Route::prefix('quarkions')->group(function () {
        /**
         * Agenda routes.
         */
        Route::prefix('agenda')->group(function () {
            Route::get('', [QuarkionsController::class, 'agendaIndex'])->name('admin.quarkions.agenda.index');
            Route::get('events', [QuarkionsController::class, 'agendaEvents'])->name('admin.quarkions.agenda.events');
            Route::get('create', [QuarkionsController::class, 'agendaCreate'])->name('admin.quarkions.agenda.create');
            Route::post('', [QuarkionsController::class, 'agendaStore'])->name('admin.quarkions.agenda.store');
            Route::get('{id}', [QuarkionsController::class, 'agendaShow'])->name('admin.quarkions.agenda.show');
            Route::get('{id}/edit', [QuarkionsController::class, 'agendaEdit'])->name('admin.quarkions.agenda.edit');
            Route::put('{id}', [QuarkionsController::class, 'agendaUpdate'])->name('admin.quarkions.agenda.update');
            Route::delete('{id}', [QuarkionsController::class, 'agendaDestroy'])->name('admin.quarkions.agenda.destroy');
            Route::post('sync-google', [QuarkionsController::class, 'agendaSyncGoogle'])->name('admin.quarkions.agenda.sync-google');
            Route::post('import-google', [QuarkionsController::class, 'agendaImportGoogle'])->name('admin.quarkions.agenda.import-google');
        });

        /**
         * WhatsApp routes.
         */
        Route::prefix('whatsapp')->group(function () {
            // Interface principal
            Route::get('', [QuarkionsController::class, 'whatsappIndex'])->name('admin.quarkions.whatsapp.index');
            Route::get('configuration', [QuarkionsController::class, 'whatsappConfiguration'])->name('admin.quarkions.whatsapp.configuration');
            Route::get('qrcode', [QuarkionsController::class, 'whatsappQrCode'])->name('admin.quarkions.whatsapp.qrcode');
            Route::get('chat/{leadId}', [QuarkionsController::class, 'whatsappChat'])->name('admin.quarkions.whatsapp.chat');
            
            // API endpoints
            Route::get('test-connection', [QuarkionsController::class, 'whatsappTestConnection'])->name('admin.quarkions.whatsapp.test-connection');
            Route::get('status', [QuarkionsController::class, 'whatsappGetStatus'])->name('admin.quarkions.whatsapp.status');
            Route::post('webhook', [QuarkionsController::class, 'whatsappWebhook'])->name('admin.quarkions.whatsapp.webhook');
            
            // Conversas e mensagens
            Route::get('conversations', [QuarkionsController::class, 'whatsappConversations'])->name('admin.quarkions.whatsapp.conversations');
            Route::get('conversations/{id}', [QuarkionsController::class, 'whatsappConversationHistory'])->name('admin.quarkions.whatsapp.conversation.history');
            Route::post('send-message', [QuarkionsController::class, 'whatsappSendMessage'])->name('admin.quarkions.whatsapp.send-message');
            Route::post('conversations/{id}/mark-read', [QuarkionsController::class, 'whatsappMarkAsRead'])->name('admin.quarkions.whatsapp.mark-read');
            Route::patch('conversations/{id}/status', [QuarkionsController::class, 'whatsappUpdateStatus'])->name('admin.quarkions.whatsapp.update-status');
        });

        /**
         * Agentes IA routes.
         */
        Route::prefix('agentes')->group(function () {
            Route::get('', [QuarkionsController::class, 'agentesIndex'])->name('admin.quarkions.agentes.index');
            Route::get('create', [QuarkionsController::class, 'agentesCreate'])->name('admin.quarkions.agentes.create');
            Route::post('', [QuarkionsController::class, 'agentesStore'])->name('admin.quarkions.agentes.store');
            Route::get('dashboard', [QuarkionsController::class, 'agentesDashboard'])->name('admin.quarkions.agentes.dashboard');
            Route::get('{id}', [QuarkionsController::class, 'agentesShow'])->name('admin.quarkions.agentes.show');
            Route::get('{id}/edit', [QuarkionsController::class, 'agentesEdit'])->name('admin.quarkions.agentes.edit');
            Route::put('{id}', [QuarkionsController::class, 'agentesUpdate'])->name('admin.quarkions.agentes.update');
            Route::delete('{id}', [QuarkionsController::class, 'agentesDestroy'])->name('admin.quarkions.agentes.destroy');
        });
    });
});

