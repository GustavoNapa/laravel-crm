<?php

use Illuminate\Support\Facades\Route;
use Webkul\Admin\Http\Controllers\QuarkionsController;

Route::group([
    'middleware' => ['web', 'admin_locale', 'user'],
    'prefix'     => 'quarkions'
], function () {

    /**
     * Agenda routes
     */
    Route::get('/agenda', [QuarkionsController::class, 'agendaIndex'])->name('admin.quarkions.agenda.index');
    Route::post('/agenda', [QuarkionsController::class, 'agendaStore'])->name('admin.quarkions.agenda.store');
    Route::put('/agenda/{id}', [QuarkionsController::class, 'agendaUpdate'])->name('admin.quarkions.agenda.update');
    Route::delete('/agenda/{id}', [QuarkionsController::class, 'agendaDestroy'])->name('admin.quarkions.agenda.destroy');
    Route::get('/agenda/events', [QuarkionsController::class, 'agendaEvents'])->name('admin.quarkions.agenda.events');
    Route::post('/agenda/sync-google', [QuarkionsController::class, 'agendaSyncGoogle'])->name('admin.quarkions.agenda.sync-google');
    Route::post('/agenda/import-google', [QuarkionsController::class, 'agendaImportGoogle'])->name('admin.quarkions.agenda.import-google');

    /**
     * WhatsApp routes
     */
    Route::get('/whatsapp', [QuarkionsController::class, 'whatsappWeb'])->name('admin.quarkions.whatsapp.index');
    Route::get('/whatsapp/inbox', [QuarkionsController::class, 'whatsappIndex'])->name('admin.quarkions.whatsapp.inbox');
    Route::get('/whatsapp/chat/{leadId}', [QuarkionsController::class, 'whatsappChat'])->name('admin.quarkions.whatsapp.chat');
    Route::get('/whatsapp/qrcode', [QuarkionsController::class, 'whatsappQrCode'])->name('admin.quarkions.whatsapp.qrcode');
    Route::get('/whatsapp/configuration', [QuarkionsController::class, 'whatsappConfiguration'])->name('admin.quarkions.whatsapp.configuration');
    Route::get('/whatsapp/status', [QuarkionsController::class, 'whatsappGetStatus'])->name('admin.quarkions.whatsapp.status');
    Route::get('/whatsapp/test-connection', [QuarkionsController::class, 'whatsappTestConnection'])->name('admin.quarkions.whatsapp.test-connection');
    Route::post('/whatsapp/test-webhook', [QuarkionsController::class, 'whatsappTestWebhook'])->name('admin.quarkions.whatsapp.test-webhook');
    Route::post('/whatsapp/webhook', [QuarkionsController::class, 'whatsappWebhook'])->name('admin.quarkions.whatsapp.webhook');
    
    // WhatsApp Web API endpoints
    Route::get('/whatsapp/conversations', [QuarkionsController::class, 'whatsappConversations'])->name('admin.quarkions.whatsapp.conversations');
    Route::get('/whatsapp/conversations/{id}', [QuarkionsController::class, 'whatsappConversationHistory'])->name('admin.quarkions.whatsapp.conversation.history');
    Route::get('/whatsapp/messages/{id}', [QuarkionsController::class, 'whatsappMessages'])->name('admin.quarkions.whatsapp.messages');
    Route::post('/whatsapp/send-message', [QuarkionsController::class, 'whatsappSendMessage'])->name('admin.quarkions.whatsapp.send-message');
    Route::post('/whatsapp/conversations/{id}/mark-read', [QuarkionsController::class, 'whatsappMarkAsRead'])->name('admin.quarkions.whatsapp.mark-read');
    Route::patch('/whatsapp/conversations/{id}/status', [QuarkionsController::class, 'whatsappUpdateStatus'])->name('admin.quarkions.whatsapp.update-status');

    /**
     * Agentes IA routes
     */
    Route::get('/agentes', [QuarkionsController::class, 'agentesIndex'])->name('admin.quarkions.agentes.index');
    Route::get('/agentes/create', [QuarkionsController::class, 'agentesCreate'])->name('admin.quarkions.agentes.create');
    Route::get('/agentes/dashboard', [QuarkionsController::class, 'agentesDashboard'])->name('admin.quarkions.agentes.dashboard');
    Route::post('/agentes', [QuarkionsController::class, 'agentesStore'])->name('admin.quarkions.agentes.store');
    Route::put('/agentes/{id}', [QuarkionsController::class, 'agentesUpdate'])->name('admin.quarkions.agentes.update');
    Route::delete('/agentes/{id}', [QuarkionsController::class, 'agentesDestroy'])->name('admin.quarkions.agentes.destroy');
});

