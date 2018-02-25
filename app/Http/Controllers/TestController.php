<?php

namespace App\Http\Controllers;

use App\Services\HighFreqStrategy\BuyGoUpService;
use App\Services\HighFreqStrategy\ShotLineService;
use App\Services\TradePlatform\GateIoService;

class TestController extends Controller
{
    // https://github.com/richox/okcoin-leeks-reaper
    public function testLeeksReaper()
    {
        $pair = 'ETH_USDT';
//        BuyGoUpService::GateIoLeeksReaper();
//        BuyGoUpService::BinanceLeeksReaper();
//        BuyGoUpService::GateIoShotLineRobot();
//        GateIoService::getOpenOrdersExist('gtc_usdt');
//        $res = BuyGoUpService::GateIoShotLineRobot($pair);
        $api = app('Binance');
        dd($api->depth('ETHUSDT'));
        $res = ShotLineService::BinanceShotLine2($pair);
        dd($res);
    }
}