<?php

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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::apiResources([
    'users' => Api\AuthController::class,
]);

Route::post('/login', 'Api\AuthController@login');
Route::put('/change-password', 'Api\AuthController@changePassword');


    Route::apiResource('/products', 'Api\ProductController');
    Route::apiResource('/orders', 'Api\OrderController');

    // Route::group(['middleware' => 'can:detail-distri-client'], function () {
    // Route::group(['middleware' => ['role:Cliente']], function () {
    
        // Admin routes
        Route::get('/list-distri-client', 'Api\AdminController@listDistriClient');
        Route::get('/detail-distri-client/{id}', 'Api\AdminController@detailDistriClient');
    
    // });