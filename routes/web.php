<?php

use Illuminate\Support\Facades\Response;
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

Route::get('/party.json', static function () {
    // 'connectionId' => 'f405cb65-d589-4c65-bb81-b59d6ff0174d',
    // 'startDate' => '2021-04-01',
    // 'endDate' => '2021-05-25',

    return Response::json(\array_map(static fn (int $i): array => [
        'shippingCostsNet' => $i * 3.32,
        'shippingCostsGross' => $i * 3.95,
        'shippingCity' => ['Bremen', 'Berlin', 'Düsseldorf', 'London'][\random_int(0, 3)],
        'shippingCountry' => 'DE',
        'billingCity' => ['Bremen', 'Berlin', 'Düsseldorf', 'London'][\random_int(0, 3)],
        'billingCountry' => 'DE',
        'customerNumber' => '2783016',
        'customerAffiliate' => null,
        'customerGroup' => 'Endkunden',
        'customerOrigin' => ['https://www.google.com/search?q=HEPTACOM+ist+cool', 'https://www.yandex.ru/', 'https://bing.com'][\random_int(0, 2)],
        'salesChannel' => 'https://heptacom-fanshop.shopware.cloud',
        'language' => ['de-DE', 'nl-NL', 'jp-JP'][\random_int(0, 2)],
        'voucherNumber' => null,
        'voucherAmount' => .0,
        'paymentMethod' => ['Apple Pay', 'PAYPAL', 'Credit Card'][\random_int(0, 2)],
        'orderNumber' => (string) (23200+$i),
        'totalAmountNet' => $i * 41.17,
        'totalAmountGross' => $i * 48.99,
        'orderTime' => date_create('2021-06-27 13:41:14')->add(new \DateInterval('P'.((int) floor($i / 3.)).'D'))->format('Y-m-d H:i:s'),
    ], \range(1, 100)));
});
