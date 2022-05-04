<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PayPalController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route::post('/v1/process-transaction', [PayPalController::class, 'processTransaction'])->name('processTransaction');
Route::post('/v1/create-transaction', [PayPalController::class, 'createTransaction'])->name('createTransaction');
Route::post('/v1/process-transaction', [PayPalController::class, 'processTransaction'])->name('processTransaction');
Route::post('/v1/success-transaction', [PayPalController::class, 'successTransaction'])->name('successTransaction');
Route::get('/v1/cancel-transaction', [PayPalController::class, 'cancelTransaction'])->name('cancelTransaction');
