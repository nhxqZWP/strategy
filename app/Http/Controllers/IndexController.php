<?php

namespace App\Http\Controllers;

use App\Services\TradePlatform\GateIoService;

class IndexController extends Controller
{
    public function getIndex()
    {
        $pair = 'ETH_USDT';
        // 此交易对钱包余额
        $api = app('Binance');
        $wallet = $api->balances();
        $coin1 = $wallet[explode('_',$pair)[0]];
        $coin2 = $wallet[explode('_',$pair)[1]];
        $data = [
            'wallet1' => ['coin1' => $coin1, 'coin2' => $coin2]
        ];
        return view('welcome', $data);
    }
}