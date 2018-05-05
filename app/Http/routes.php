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
//    Route::get('/', 'IndexController@getIndex');
    Route::get('/', 'IndexController@getIndexNew');

     //huobi
     Route::get('/huobi/depth', 'HuobiStrategyController@getHuobiDepth');
     Route::get('/huobi/depth/all', 'HuobiStrategyController@getAllDepth');

     //binance
     Route::get('/binance/depth/all', 'BinanceStrategyController@getAllDepth');

    Route::get('/switch', 'StrategyController@updateRunStatus');
    Route::get('/switch_new', 'StrategyController@updateRunStatusNew');
    Route::get('/cancel/order', 'StrategyController@cancelOneOrder');
    Route::post('/timelimit', 'StrategyController@timeLimit');
    Route::post('/getpercent', 'StrategyController@getpercent');
    Route::post('/quantity', 'StrategyController@postQuantity');
    Route::post('/binance/params', 'StrategyController@postParams');
    Route::post('/binance/params_new', 'StrategyController@postParamsNew');
    Route::post('/binance/profit', 'StrategyController@postProfit');
    Route::post('/binance/cancelSell', 'StrategyController@postCancelSell');
    Route::get('/init', 'StrategyController@getInit');
    Route::post('/stop_loss', 'StrategyController@stopLossOffset');

    Route::get('/gtc_usdt', 'StrategyController@getGateIoOneCoin');
    Route::get('/eth_usdt', 'StrategyController@getBinanceOneCoin');
    Route::get('/eth_usdt_new', 'StrategyController@getBinanceOneCoinNew');
    Route::get('/btc_usdt', 'StrategyController@getBinanceOneCoinNew2');
});

Route::get('test', 'TestController@testLeeksReaper');

