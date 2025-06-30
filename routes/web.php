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
Route::get('/leads/list', function() {
    $leads = \App\Models\LeadQuarkions::select('id', 'nome', 'telefone')->get();
    return response()->json(['leads' => $leads]);
})->name('leads.list');

