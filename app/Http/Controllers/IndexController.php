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

        // test show k线图
//         $ticks = $api->candlesticks("ETHUSDT", "1m");
//         $endSecond = array_slice($ticks,-2,1);
//         dd($endSecond[0]);
//         dd($endSecond[0]['close'] - $endSecond[0]['open']);
//         $data = [];
//         foreach ($ticks as $k => $t) {
//              $k = date('Y-m-d H:i:s', $k/1000);
//              $data[$k] = $t;
//         }
//         krsort($ticks);
//         dd($ticks);

        $coin1 = $wallet[explode('_',$pair)[0]];
        $coin2 = $wallet[explode('_',$pair)[1]];

        $list = \DB::table('everyday_profit')->orderBy('created_at', 'desc')->paginate(7);

        $data = [
            'wallet1' => ['coin1' => $coin1, 'coin2' => $coin2, 'list' => $list]
        ];
        return view('welcome', $data);
    }

    public function getIndexNew()
    {
         return view('welcome_new');
    }
}