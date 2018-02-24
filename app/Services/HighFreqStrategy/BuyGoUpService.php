<?php

namespace App\Services\HighFreqStrategy;

use App\Services\ConsoleService;
use App\Services\TradePlatform\BinanceService;
use App\Services\TradePlatform\GateIo;
use App\Services\TradePlatform\GateIoService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class BuyGoUpService
{
    // https://github.com/richox/okcoin-leeks-reaper
    public static function GateIoLeeksReaper()
    {
        $pair = 'eos_usdt';
        // 因为平台交易手续费的存在 高频交易已经无法使用 现在尝试降低频率
//        $orderParams = GateIoService::getOrderParams($pair);
//        $ticker = GateIo::get_ticker($pair);
//dd($ticker);
        // 获取委托挂单
//        $orderBook = GateIo::get_orderbook($pair);
        // 获取币种余额
//        $balance = GateIoService::getCoinBalance($pair);
        // 获取下单价格
        $depth = GateIoService::getPrice($pair);
        dd($depth);
    }

    const START_INIT_GTC = 5500;
    const START_INIT_USDT = 507;
    const CANCEL_ALL_SELL = 0;
    const CANCEL_ALL_BUY = 1;

    public static function GateIoShotLineRobot($pair)
    {
        set_time_limit(0);
        $lastPrice = GateIo::get_ticker($pair)['last'];

        // 账户此交易对总共的币与钱 (coin1 coin2)
        $pairBalance = GateIoService::getPairBalance($pair);
        $coin1Total = $pairBalance['coin1_total'];
        $coin2Total = $pairBalance['coin2_total'];
        if ($coin1Total < self::START_INIT_GTC || $coin2Total < self::START_INIT_USDT) {
            return ['result' => false, 'message' => 'system stop, because coin left less than init'];
        }

        // 获取下单价格
        while (is_null($price = GateIoService::getPrice($pair, $pairBalance, $lastPrice))) {
            sleep(1);
            $price = GateIoService::getPrice($pair, $pairBalance, $lastPrice);
        }
        $sellPrice = $price['sell_price'];
        $buyPrice = $price['buy_price'];

        // 撤销原来的单子
        $cancelBuy = false;
        while($cancelBuy != true) {
            $cancelBuy = GateIo::cancel_all_orders(self::CANCEL_ALL_BUY, $pair)['result'];
        }
        $cancelSell = false;
        while($cancelSell != true) {
            $cancelSell = GateIo::cancel_all_orders(self::CANCEL_ALL_SELL, $pair)['result'];
        }

        // 下卖单 max 1000000 USDT
        $orderSell = GateIo::sell($pair, $sellPrice, $coin1Total);
        if ($orderSell['result'] == 'false' || $orderSell['result'] == false) {
            goto end;
        }

        // 下买单 min 10USDT
        $orderBuy = GateIo::buy($pair, $buyPrice, $coin2Total / $lastPrice);
        if ($orderBuy['result'] == 'false') {
            $cancelSell = false;
            while($cancelSell != true) {
                $cancelSell = GateIo::cancel_all_orders(self::CANCEL_ALL_SELL, $pair)['result'];
            }
            return ['result' => false, 'message' => $orderBuy['message'], 'type' => 'buy_order'];
        }

        // 记录挂单单号
        Redis::set('gate:order_number:sell_' . $pair, $orderSell['orderNumber']);
        Redis::set('gate:order_number:buy_' . $pair, $orderBuy['orderNumber']);

        // 设定此次挂单时间
        $timeLimit = Redis::get(ConsoleService::GTC_RUN_TIME_LIMIT_VALUE);
        if (is_null($timeLimit)) $timeLimit = 60 * 20;
        Redis::setex(ConsoleService::GTC_RUN_TIME_LIMIT_KEY, $timeLimit, '1');
        return ['result' => true, 'message' => $price];

        end: return ['result' => false, 'message' => $orderSell['message'], 'type' => 'sell_order'];
    }

    public static function BinanceLeeksReaper()
    {
        $res = BinanceService::ping();
    }
}