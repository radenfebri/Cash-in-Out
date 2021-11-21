<?php

use App\Http\Controllers\Auth\MeController;
use App\Http\Controllers\CashController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Route};

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Auth::loginUsingId(2);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('me', [MeController::class, '__invoke']);

    Route::prefix('cash')->group(function () {
        Route::get('', [CashController::class, 'index']);
        Route::post('create', [CashController::class, 'store']);
    });

});
