<?php

use App\Http\Controllers\WhatsAppController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// WhatsApp Webhook Routes
Route::post('/webhook/whatsapp', [WhatsAppController::class, 'webhook'])->name('whatsapp.webhook');

// WhatsApp API Routes
Route::prefix('whatsapp')->group(function () {
    Route::get('/conversations', [WhatsAppController::class, 'getConversations'])->name('whatsapp.conversations');
    Route::get('/conversations/{id}/messages', [WhatsAppController::class, 'getMessages'])->name('whatsapp.messages');
    Route::post('/send', [WhatsAppController::class, 'sendMessage'])->name('whatsapp.send');
    Route::get('/status', [WhatsAppController::class, 'getStatus'])->name('whatsapp.status');
});
