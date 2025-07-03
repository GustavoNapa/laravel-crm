<?php

use Illuminate\Support\Facades\Route;

/**
 * Auth routes.
 */
require 'auth-routes.php';

/**
 * Leads routes.
 */
require 'leads-routes.php';

/**
 * Email routes.
 */
require 'mail-routes.php';

/**
 * Settings routes.
 */
require 'settings-routes.php';

/**
 * Products routes.
 */
require 'products-routes.php';

/**
 * Contacts routes.
 */
require 'contacts-routes.php';

/**
 * Activities routes.
 */
require 'activities-routes.php';

/**
 * Quotes routes.
 */
require 'quote-routes.php';

/**
 * Configuration routes.
 */
require 'configuration-routes.php';

/**
 * Quarkions routes.
 */
require 'quarkions-routes.php';

/**
 * Test route for debugging
 */
Route::get('test-whatsapp', function() {
    return response()->json([
        'status' => 'success',
        'message' => 'Rota de teste funcionando',
        'timestamp' => now()
    ]);
})->name('admin.test.whatsapp');

/**
 * Rest routes.
 */
require 'rest-routes.php';
