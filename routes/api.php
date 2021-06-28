<?php

use App\Http\Controllers\Shopware6Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
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

Route::prefix('v1')->name('api.v1.')->group(static function (): void {
    Route::view('shopware6/manifest.xml', 'integration.shopware-6.manifest-xml')->name('shopware6.manifest');
    Route::get('shopware6/wizard', [Shopware6Controller::class, 'wizard'])->name('shopware6.wizard');
    Route::get('shopware6/register', [Shopware6Controller::class, 'register'])->name('shopware6.register');
    Route::post('shopware6/confirm/{internalId}', [Shopware6Controller::class, 'confirm'])->name('shopware6.confirm');
    Route::get('shopware6/order/{shop}', [Shopware6Controller::class, 'order'])->name('shopware6.order');
});
