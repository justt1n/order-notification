<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/', 'App\Http\Controllers\OrderNotificationController@receiveNotification');
Route::get('/', function () {
    return 200;
});
