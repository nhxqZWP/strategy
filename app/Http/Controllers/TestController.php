<?php

namespace App\Http\Controllers;

use App\Services\HighFreqStrategy\BuyGoUpService;
use App\Services\TradePlatform\GateIoService;

class TestController extends Controller
{
    // https://github.com/richox/okcoin-leeks-reaper
    public function testLeeksReaper()
    {
//        BuyGoUpService::GateIoLeeksReaper();
//        BuyGoUpService::BinanceLeeksReaper();
//        BuyGoUpService::GateIoShotLineRobot();
        GateIoService::getOpenOrdersExist('gtc_usdt');
    }
}