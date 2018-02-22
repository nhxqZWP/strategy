<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

require __DIR__ . '/routes-rbac.php';
require __DIR__ . '/routes-common.php';

Route::group(['middleware'=>'rbac'], function() {
    Route::get('/', function () {
        return view('welcome');
    });
    Route::get('/switch', 'StrategyController@updateRunStatus');
    // gate.io
    Route::get('/gtc_usdt', 'StrategyController@getGateIoOneCoin');

});

