<?php

use Illuminate\Support\Facades\Route;
use Webkul\Admin\Http\Controllers\QuarkionsController;

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
            Route::get('create', [QuarkionsController::class, 'agendaCreate'])->name('admin.quarkions.agenda.create');
            Route::post('', [QuarkionsController::class, 'agendaStore'])->name('admin.quarkions.agenda.store');
            Route::get('{id}', [QuarkionsController::class, 'agendaShow'])->name('admin.quarkions.agenda.show');
            Route::get('{id}/edit', [QuarkionsController::class, 'agendaEdit'])->name('admin.quarkions.agenda.edit');
            Route::put('{id}', [QuarkionsController::class, 'agendaUpdate'])->name('admin.quarkions.agenda.update');
            Route::delete('{id}', [QuarkionsController::class, 'agendaDestroy'])->name('admin.quarkions.agenda.destroy');
        });

        /**
         * WhatsApp routes.
         */
        Route::prefix('whatsapp')->group(function () {
            Route::get('', [QuarkionsController::class, 'whatsappIndex'])->name('admin.quarkions.whatsapp.index');
            Route::get('configuration', [QuarkionsController::class, 'whatsappConfiguration'])->name('admin.quarkions.whatsapp.configuration');
            Route::get('test-connection', [QuarkionsController::class, 'whatsappTestConnection'])->name('admin.quarkions.whatsapp.test-connection');
            Route::get('chat/{leadId}', [QuarkionsController::class, 'whatsappChat'])->name('admin.quarkions.whatsapp.chat');
            Route::post('send-message', [QuarkionsController::class, 'whatsappSendMessage'])->name('admin.quarkions.whatsapp.send');
            Route::get('qrcode', [QuarkionsController::class, 'whatsappQrCode'])->name('admin.quarkions.whatsapp.qrcode');
            Route::post('webhook', [QuarkionsController::class, 'whatsappWebhook'])->name('admin.quarkions.whatsapp.webhook');
            Route::post('create-instance', [QuarkionsController::class, 'whatsappCreateInstance'])->name('admin.quarkions.whatsapp.create-instance');
            Route::post('set-webhook', [QuarkionsController::class, 'whatsappSetWebhook'])->name('admin.quarkions.whatsapp.set-webhook');
            Route::get('status', [QuarkionsController::class, 'whatsappGetStatus'])->name('admin.quarkions.whatsapp.status');
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
