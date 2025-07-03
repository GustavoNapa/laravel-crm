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

// Rota para listar leads (para o select da agenda)
Route::get('/leads/list', function () {
    $leads = \App\Models\LeadQuarkions::select('id', 'nome', 'telefone')->get();

    return response()->json(['leads' => $leads]);
})->name('leads.list');

// Test routes for debugging
Route::get('/test/whatsapp-config', function () {
    return response()->json([
        'config' => [
            'base_url' => core()->getConfigData('general.whatsapp.evolution_api.base_url')
                ?? config('whatsapp.evolution_base_url'),
            'instance_name' => core()->getConfigData('general.whatsapp.evolution_api.instance_name')
                ?? config('whatsapp.instance_name'),
            'token_set' => ! empty(core()->getConfigData('general.whatsapp.evolution_api.token')
                ?? config('whatsapp.evolution_token')),
        ],
        'env_values' => [
            'EVOLUTION_BASE_URL'     => env('EVOLUTION_BASE_URL'),
            'WHATSAPP_INSTANCE_NAME' => env('WHATSAPP_INSTANCE_NAME'),
            'EVOLUTION_TOKEN_SET'    => ! empty(env('EVOLUTION_TOKEN')),
        ],
    ]);
});

Route::get('/test/whatsapp-status', function () {
    $service = new \App\Services\WhatsAppService;
    $status = $service->getInstanceStatus();

    return response()->json($status);
});
