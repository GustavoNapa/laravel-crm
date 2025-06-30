<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Rotas para Agenda
Route::resource('agenda', App\Http\Controllers\AgendaController::class);

// Rotas para WhatsApp
Route::prefix('whatsapp')->group(function () {
    Route::get('/', [App\Http\Controllers\WhatsAppController::class, 'index'])->name('whatsapp.index');
    Route::get('/chat/{leadId}', [App\Http\Controllers\WhatsAppController::class, 'chat'])->name('whatsapp.chat');
    Route::post('/send-message', [App\Http\Controllers\WhatsAppController::class, 'sendMessage'])->name('whatsapp.send');
    Route::get('/qrcode', [App\Http\Controllers\WhatsAppController::class, 'qrCode'])->name('whatsapp.qrcode');
    Route::post('/webhook', [App\Http\Controllers\WhatsAppController::class, 'webhook'])->name('whatsapp.webhook');
});

// Rotas para Agentes IA
Route::resource('agentes', App\Http\Controllers\AgentesController::class);
Route::get('/agentes-dashboard', [App\Http\Controllers\AgentesController::class, 'dashboard'])->name('agentes.dashboard');


// Rotas adicionais para WhatsApp
Route::prefix('whatsapp')->group(function () {
    Route::post('/create-instance', [App\Http\Controllers\WhatsAppController::class, 'createInstance'])->name('whatsapp.create-instance');
    Route::post('/set-webhook', [App\Http\Controllers\WhatsAppController::class, 'setWebhook'])->name('whatsapp.set-webhook');
    Route::get('/status', [App\Http\Controllers\WhatsAppController::class, 'getStatus'])->name('whatsapp.status');
});

