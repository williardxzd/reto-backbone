<?php

use App\Http\Controllers\ZipCodeController;
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

Route::controller(ZipCodeController::class)
    ->group(function () {
    Route::prefix('zip-codes')
        ->as('zip-codes.')
        ->group(function () {
            Route::get('/', 'index')->name('index');

            Route::prefix('{zip_code}')->group(function() {
                Route::get('/', 'show')->name('show');
            });
        });
});


Route::get('/live', function (Request $request) {
    return ['success' => true];
});
