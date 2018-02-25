<?php

namespace App\Services\HighFreqStrategy;

use App\Services\ConsoleService;
use App\Services\LockService;
use App\Services\TradePlatform\BinanceService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ShotLineService
{
    public static function testApi($pair)
    {
        $api = app('Binance');
//        $ticker = $api->prices(); // $ticker['ETHUSDT']
//        $balance = $api->balances();
        $depth = $api->depth($pair);
        dd($depth);
    }

//    public static function BinanceShotLine($pair)
//    {
//        $api = app('Binance');
//        $ticker = implode('', explode('_', $pair));  // pair - ETH_USDT  ticker - EHTUSDT
//        set_time_limit(0);
//        $lastPrice = $api->prices()[$ticker];
//
//        // 账户此交易对总共的币与钱 (coin1 coin2)
//        $pairBalance = BinanceService::getBalanceAvail($pair);
//        $coin1Total = $pairBalance['coin1_total'];
//        $coin2Total = $pairBalance['coin2_total'];
//
//        // 获取下单价格
//        while (is_null($price = BinanceService::getPrice($pair, $ticker, $pairBalance, $lastPrice))) {
//            sleep(1);
//            $price = BinanceService::getPrice($pair, $ticker, $pairBalance, $lastPrice);
//        }
//        dd($price);
//        $sellPrice = $price['sell_price'];
//        $buyPrice = $price['buy_price'];
//
//        // 撤销原来的单子
//        $cancelBuy = false;
//        while($cancelBuy != true) {
//            $cancelBuy = GateIo::cancel_all_orders(self::CANCEL_ALL_BUY, $pair)['result'];
//        }
//        $cancelSell = false;
//        while($cancelSell != true) {
//            $cancelSell = GateIo::cancel_all_orders(self::CANCEL_ALL_SELL, $pair)['result'];
//        }
//
//        // 下卖单 max 1000000 USDT
//        $orderSell = GateIo::sell($pair, $sellPrice, $coin1Total);
//        if ($orderSell['result'] == 'false' || $orderSell['result'] == false) {
//            goto end;
//        }
//
//        // 下买单 min 10USDT
//        $orderBuy = GateIo::buy($pair, $buyPrice, $coin2Total / $lastPrice);
//        if ($orderBuy['result'] == 'false') {
//            $cancelSell = false;
//            while($cancelSell != true) {
//                $cancelSell = GateIo::cancel_all_orders(self::CANCEL_ALL_SELL, $pair)['result'];
//            }
//            return ['result' => false, 'message' => $orderBuy['message'], 'type' => 'buy_order'];
//        }
//
//        // 记录挂单单号
//        Redis::set('gate:order_number:sell_' . $pair, $orderSell['orderNumber']);
//        Redis::set('gate:order_number:buy_' . $pair, $orderBuy['orderNumber']);
//
//        // 设定此次挂单时间
//        $timeLimit = Redis::get(ConsoleService::GTC_RUN_TIME_LIMIT_VALUE);
//        if (is_null($timeLimit)) $timeLimit = 60 * 20;
//        Redis::setex(ConsoleService::GTC_RUN_TIME_LIMIT_KEY, $timeLimit, '1');
//        return ['result' => true, 'message' => $price];
//
//        end: return ['result' => false, 'message' => $orderSell['message'], 'type' => 'sell_order'];
//    }

    public static function BinanceShotLine2($pair)
    {
        ini_set('memory_limit', '500M'); //内存限制
        set_time_limit(0);
        $api = app('Binance');
        $ticker = implode('', explode('_', $pair));  // pair - ETH_USDT  ticker - EHTUSDT

        // 加锁
        $isLock = LockService::lock('binance:lock:shot_1', 1, 1);
        if (!$isLock) {
            return ['result' => true, 'message' => 'trigger lock 1'];
        }

        $sellNumber = Redis::get('binance:sell:number_'.$pair.'1');
        $sellStatus = $api->orderStatus($ticker, $sellNumber);
        if (!is_null($sellNumber) && $sellStatus['side'] == 'SELL' && ($sellStatus['status'] == 'NEW' || $sellStatus['status'] == 'PARTIALLY_FILLED')) {
            // 有未完成卖单
            return ['result' => true, 'message' => 'have unfinished sell order'];
        } else {
            $buyNumber = Redis::get('binance:buy:number_'.$pair.'1');
            $buyStatus = $api->orderStatus($ticker, $buyNumber);
            if (!is_null($buyNumber) && $buyStatus['side'] == 'BUY' && ($buyStatus['status'] == 'NEW' || $buyStatus['status'] == 'PARTIALLY_FILLED')) {
                // 无卖单 有未完成的买单
                  //判断是否到了最长买单时间
                $runTimeLimit = Redis::get(ConsoleService::BINANCE_RUN_TIME_LIMIT_KEY.'1');
                if (is_null($runTimeLimit)) {
                    $api->cancel($ticker, $buyNumber);
                    Redis::set('binance:buy:mark_'.$pair.'1', 2);
                    return ['result' => true, 'message' => 'auto cancel buy order'];
                }
                return ['result' => true, 'message' => 'have unfinished buy order'];
            }
            $quantity = Redis::get('binance:buy:quantity_'.$pair.'1'); //买卖单数量
            if (is_null($quantity)) $quantity = 0.02;  // 买卖1个eth

            // 无卖单或卖单完成 且 无买单或买单完成或买单取消 则下买单
            $noSell = is_null($sellNumber) || isset($sellStatus['status']) && $sellStatus['status'] == 'FILLED';
            $noBuy = is_null($buyNumber) || (isset($buyStatus['status'])&&($buyStatus['status'] == 'FILLED' || $buyStatus['status'] == 'CANCELED'));
            $buyDeal = Redis::get('binance:buy:mark_'.$pair.'1');
            if ($noSell && $noBuy && (is_null($buyDeal) || $buyDeal == 2)) {
                $depth = $api->depth($ticker);
                $depthBids = array_keys($depth['bids']);
                $buyDepthNumber = Redis::get('binance:buy:offset_'.$pair.'1'); //买单偏移数
                if (is_null($buyDepthNumber)) $buyDepthNumber = 1;
                $price = $depthBids[$buyDepthNumber];
                $res = $api->buy($ticker, $quantity, $price);
                if ($res['status'] == 'NEW' || $res['status'] == 'PARTIALLY_FILLED' || $res['status'] == 'FILLED') {
                    Redis::set('binance:buy:number_'.$pair.'1', $res['orderId']);
                    Redis::set('binance:buy:price_'.$pair.'1', $res['price']);
                    Redis::set('binance:buy:mark_'.$pair.'1', 1); //标记买单创建
                    // 设定此次挂单时间
                    $timeLimit = Redis::get(ConsoleService::BINANCE_RUN_TIME_LIMIT_VALUE);
                    if (is_null($timeLimit)) $timeLimit = 30;
                    Redis::setex(ConsoleService::BINANCE_RUN_TIME_LIMIT_KEY.'1', $timeLimit, '1');
                    return ['result' => true, 'message' => 'create buy order success '.json_encode($res)];
                } else {
                    return ['result' => false, 'message' => 'create buy order fail '.json_encode($res)];
                }
            } else {
                // 有完成的买单 则下卖单
                if (!is_null($buyNumber) && $buyStatus['side'] == 'BUY' && $buyStatus['status'] == 'FILLED') {
                    $sellDepthNumber = Redis::get('binance:sell:offset_'.$pair); //卖单偏移值(净利润)
                    if (is_null($sellDepthNumber)) $sellDepthNumber = 0.2;
                    $sellPrice = Redis::get('binance:buy:price_'.$pair.'1') * (1+0.001) + $sellDepthNumber;
                    $sellPrice = number_format($sellPrice, 2, '.', '');
                    $res = $api->sell($ticker, $quantity, $sellPrice);
                    if (isset($res['msg'])) {
                        return ['result' => false, 'message' => $res['msg']];
                    }
                    if ($res['status'] == 'NEW' || $res['status'] == 'PARTIALLY_FILLED' || $res['status'] == 'FILLED') {
                        Redis::set('binance:sell:number_'.$pair.'1', $res['orderId']);
                        Redis::set('binance:buy:mark_'.$pair.'1', 2); //标记对应买单处理了
                        return ['result' => true, 'message' => 'create sell order success '.json_encode($res)];
                    } else {
                        return ['result' => false, 'message' => 'create sell order fail '.json_encode($res)];
                    }
                }
            }
        }
        return ['result' => false, 'message' => 'have no action'];
    }

    public static function BinanceShotLine2Two($pair)
    {
        ini_set('memory_limit', '500M'); //内存限制
        set_time_limit(0);
        $api = app('Binance');
        $ticker = implode('', explode('_', $pair));  // pair - ETH_USDT  ticker - EHTUSDT

        // 加锁
        $isLock = LockService::lock('binance:lock:shot_2', 1, 1);
        if (!$isLock) {
            return ['result' => true, 'message' => 'trigger lock 2'];
        }

        $sellNumber = Redis::get('binance:sell:number_'.$pair.'2');
        $sellStatus = $api->orderStatus($ticker, $sellNumber);
        if (!is_null($sellNumber) && $sellStatus['side'] == 'SELL' && ($sellStatus['status'] == 'NEW' || $sellStatus['status'] == 'PARTIALLY_FILLED')) {
            // 有未完成卖单
            return ['result' => true, 'message' => 'have unfinished sell order'];
        } else {
            $buyNumber = Redis::get('binance:buy:number_'.$pair.'2');
            $buyStatus = $api->orderStatus($ticker, $buyNumber);
            if (!is_null($buyNumber) && $buyStatus['side'] == 'BUY' && ($buyStatus['status'] == 'NEW' || $buyStatus['status'] == 'PARTIALLY_FILLED')) {
                // 无卖单 有未完成的买单
                //判断是否到了最长买单时间
                $runTimeLimit = Redis::get(ConsoleService::BINANCE_RUN_TIME_LIMIT_KEY.'2');
                if (is_null($runTimeLimit)) {
                    $api->cancel($ticker, $buyNumber);
                    Redis::set('binance:buy:mark_'.$pair.'2', 2);
                    return ['result' => true, 'message' => 'auto cancel buy order'];
                }
                return ['result' => true, 'message' => 'have unfinished buy order'];
            }
            $quantity = Redis::get('binance:buy:quantity_'.$pair.'2'); //买卖单数量
            if (is_null($quantity)) $quantity = 0.04;  // 买卖1个eth

            // 无卖单或卖单完成 且 无买单或买单完成或买单取消 则下买单
            $noSell = is_null($sellNumber) || isset($sellStatus['status']) && $sellStatus['status'] == 'FILLED';
            $noBuy = is_null($buyNumber) || (isset($buyStatus['status'])&&($buyStatus['status'] == 'FILLED' || $buyStatus['status'] == 'CANCELED'));
            $buyDeal = Redis::get('binance:buy:mark_'.$pair.'2');
            if ($noSell && $noBuy && (is_null($buyDeal) || $buyDeal == 2)) {
                $depth = $api->depth($ticker);
                $depthBids = array_keys($depth['bids']);
                $buyDepthNumber = Redis::get('binance:buy:offset_'.$pair.'2'); //买单偏移数
                if (is_null($buyDepthNumber)) $buyDepthNumber = 3;
                $price = $depthBids[$buyDepthNumber];
                $res = $api->buy($ticker, $quantity, $price);
                if ($res['status'] == 'NEW' || $res['status'] == 'PARTIALLY_FILLED' || $res['status'] == 'FILLED') {
                    Redis::set('binance:buy:number_'.$pair.'2', $res['orderId']);
                    Redis::set('binance:buy:price_'.$pair.'2', $res['price']);
                    Redis::set('binance:buy:mark_'.$pair.'2', 1); //标记买单创建
                    // 设定此次挂单时间
                    $timeLimit = Redis::get(ConsoleService::BINANCE_RUN_TIME_LIMIT_VALUE);
                    if (is_null($timeLimit)) $timeLimit = 30;
                    Redis::setex(ConsoleService::BINANCE_RUN_TIME_LIMIT_KEY.'2', $timeLimit, '1');
                    return ['result' => true, 'message' => 'create buy order success '.json_encode($res)];
                } else {
                    return ['result' => false, 'message' => 'create buy order fail '.json_encode($res)];
                }
            } else {
                // 有完成的买单 则下卖单
                if (!is_null($buyNumber) && $buyStatus['side'] == 'BUY' && $buyStatus['status'] == 'FILLED') {
                    $sellDepthNumber = Redis::get('binance:sell:offset_'.$pair); //卖单偏移值
                    if (is_null($sellDepthNumber)) $sellDepthNumber = 0.2;
                    $sellPrice = Redis::get('binance:buy:price_'.$pair.'2') * (1+0.001) + $sellDepthNumber;
                    $sellPrice = number_format($sellPrice, 2, '.', '');
                    $res = $api->sell($ticker, $quantity, $sellPrice);
                    if (isset($res['msg'])) {
                        return ['result' => false, 'message' => $res['msg']];
                    }
                    if ($res['status'] == 'NEW' || $res['status'] == 'PARTIALLY_FILLED' || $res['status'] == 'FILLED') {
                        Redis::set('binance:sell:number_'.$pair.'2', $res['orderId']);
                        Redis::set('binance:buy:mark_'.$pair.'2', 2); //标记对应买单处理了
                        return ['result' => true, 'message' => 'create sell order success '.json_encode($res)];
                    } else {
                        return ['result' => false, 'message' => 'create sell order fail '.json_encode($res)];
                    }
                }
            }
        }
        return ['result' => false, 'message' => 'have no action'];
    }

    public static function BinanceShotLine2Three($pair)
    {
        ini_set('memory_limit', '500M'); //内存限制
        set_time_limit(0);
        $api = app('Binance');
        $ticker = implode('', explode('_', $pair));  // pair - ETH_USDT  ticker - EHTUSDT

        // 加锁
        $isLock = LockService::lock('binance:lock:shot_3', 1, 1);
        if (!$isLock) {
            return ['result' => true, 'message' => 'trigger lock 3'];
        }

        $sellNumber = Redis::get('binance:sell:number_'.$pair.'3');
        $sellStatus = $api->orderStatus($ticker, $sellNumber);
        if (!is_null($sellNumber) && $sellStatus['side'] == 'SELL' && ($sellStatus['status'] == 'NEW' || $sellStatus['status'] == 'PARTIALLY_FILLED')) {
            // 有未完成卖单
            return ['result' => true, 'message' => 'have unfinished sell order'];
        } else {
            $buyNumber = Redis::get('binance:buy:number_'.$pair.'3');
            $buyStatus = $api->orderStatus($ticker, $buyNumber);
            if (!is_null($buyNumber) && $buyStatus['side'] == 'BUY' && ($buyStatus['status'] == 'NEW' || $buyStatus['status'] == 'PARTIALLY_FILLED')) {
                // 无卖单 有未完成的买单
                //判断是否到了最长买单时间
                $runTimeLimit = Redis::get(ConsoleService::BINANCE_RUN_TIME_LIMIT_KEY.'3');
                if (is_null($runTimeLimit)) {
                    $api->cancel($ticker, $buyNumber);
                    Redis::set('binance:buy:mark_'.$pair.'3', 2);
                    return ['result' => true, 'message' => 'auto cancel buy order'];
                }
                return ['result' => true, 'message' => 'have unfinished buy order'];
            }
            $quantity = Redis::get('binance:buy:quantity_'.$pair.'3'); //买卖单数量
            if (is_null($quantity)) $quantity = 0.06;  // 买卖1个eth

            // 无卖单或卖单完成 且 无买单或买单完成或买单取消 则下买单
            $noSell = is_null($sellNumber) || isset($sellStatus['status']) && $sellStatus['status'] == 'FILLED';
            $noBuy = is_null($buyNumber) || (isset($buyStatus['status'])&&($buyStatus['status'] == 'FILLED' || $buyStatus['status'] == 'CANCELED'));
            $buyDeal = Redis::get('binance:buy:mark_'.$pair.'3');
            if ($noSell && $noBuy && (is_null($buyDeal) || $buyDeal == 2)) {
                $depth = $api->depth($ticker);
                $depthBids = array_keys($depth['bids']);
                $buyDepthNumber = Redis::get('binance:buy:offset_'.$pair.'3'); //买单偏移数
                if (is_null($buyDepthNumber)) $buyDepthNumber = 4;
                $price = $depthBids[$buyDepthNumber];
                $res = $api->buy($ticker, $quantity, $price);
                if ($res['status'] == 'NEW' || $res['status'] == 'PARTIALLY_FILLED' || $res['status'] == 'FILLED') {
                    Redis::set('binance:buy:number_'.$pair.'3', $res['orderId']);
                    Redis::set('binance:buy:price_'.$pair.'3', $res['price']);
                    Redis::set('binance:buy:mark_'.$pair.'3', 1); //标记买单创建
                    // 设定此次挂单时间
                    $timeLimit = Redis::get(ConsoleService::BINANCE_RUN_TIME_LIMIT_VALUE);
                    if (is_null($timeLimit)) $timeLimit = 30;
                    Redis::setex(ConsoleService::BINANCE_RUN_TIME_LIMIT_KEY.'3', $timeLimit, '1');
                    return ['result' => true, 'message' => 'create buy order success '.json_encode($res)];
                } else {
                    return ['result' => false, 'message' => 'create buy order fail '.json_encode($res)];
                }
            } else {
                // 有完成的买单 则下卖单
                if (!is_null($buyNumber) && $buyStatus['side'] == 'BUY' && $buyStatus['status'] == 'FILLED') {
                    $sellDepthNumber = Redis::get('binance:sell:offset_'.$pair); //卖单偏移值
                    if (is_null($sellDepthNumber)) $sellDepthNumber = 0.2;
                    $sellPrice = Redis::get('binance:buy:price_'.$pair.'3') * (1+0.001) + $sellDepthNumber;
                    $sellPrice = number_format($sellPrice, 2, '.', '');
                    $res = $api->sell($ticker, $quantity, $sellPrice);
                    if (isset($res['msg'])) {
                        return ['result' => false, 'message' => $res['msg']];
                    }
                    if ($res['status'] == 'NEW' || $res['status'] == 'PARTIALLY_FILLED' || $res['status'] == 'FILLED') {
                        Redis::set('binance:sell:number_'.$pair.'3', $res['orderId']);
                        Redis::set('binance:buy:mark_'.$pair.'3', 2); //标记对应买单处理了
                        return ['result' => true, 'message' => 'create sell order success '.json_encode($res)];
                    } else {
                        return ['result' => false, 'message' => 'create sell order fail '.json_encode($res)];
                    }
                }
            }
        }
        return ['result' => false, 'message' => 'have no action'];
    }

    public static function BinanceShotLine2Four($pair)
    {
        ini_set('memory_limit', '500M'); //内存限制
        set_time_limit(0);
        $api = app('Binance');
        $ticker = implode('', explode('_', $pair));  // pair - ETH_USDT  ticker - EHTUSDT

        // 加锁
        $isLock = LockService::lock('binance:lock:shot_4', 1, 1);
        if (!$isLock) {
            return ['result' => true, 'message' => 'trigger lock 4'];
        }

        $sellNumber = Redis::get('binance:sell:number_'.$pair.'4');
        $sellStatus = $api->orderStatus($ticker, $sellNumber);
        if (!is_null($sellNumber) && $sellStatus['side'] == 'SELL' && ($sellStatus['status'] == 'NEW' || $sellStatus['status'] == 'PARTIALLY_FILLED')) {
            // 有未完成卖单
            return ['result' => true, 'message' => 'have unfinished sell order'];
        } else {
            $buyNumber = Redis::get('binance:buy:number_'.$pair.'4');
            $buyStatus = $api->orderStatus($ticker, $buyNumber);
            if (!is_null($buyNumber) && $buyStatus['side'] == 'BUY' && ($buyStatus['status'] == 'NEW' || $buyStatus['status'] == 'PARTIALLY_FILLED')) {
                // 无卖单 有未完成的买单
                //判断是否到了最长买单时间
                $runTimeLimit = Redis::get(ConsoleService::BINANCE_RUN_TIME_LIMIT_KEY.'4');
                if (is_null($runTimeLimit)) {
                    $api->cancel($ticker, $buyNumber);
                    Redis::set('binance:buy:mark_'.$pair.'4', 2);
                    return ['result' => true, 'message' => 'auto cancel buy order'];
                }
                return ['result' => true, 'message' => 'have unfinished buy order'];
            }
            $quantity = Redis::get('binance:buy:quantity_'.$pair.'4'); //买卖单数量
            if (is_null($quantity)) $quantity = 0.08;  // 买卖1个eth

            // 无卖单或卖单完成 且 无买单或买单完成或买单取消 则下买单
            $noSell = is_null($sellNumber) || isset($sellStatus['status']) && $sellStatus['status'] == 'FILLED';
            $noBuy = is_null($buyNumber) || (isset($buyStatus['status'])&&($buyStatus['status'] == 'FILLED' || $buyStatus['status'] == 'CANCELED'));
            $buyDeal = Redis::get('binance:buy:mark_'.$pair.'4');
            if ($noSell && $noBuy && (is_null($buyDeal) || $buyDeal == 2)) {
                $depth = $api->depth($ticker);
                $depthBids = array_keys($depth['bids']);
                $buyDepthNumber = Redis::get('binance:buy:offset_'.$pair.'4'); //买单偏移数
                if (is_null($buyDepthNumber)) $buyDepthNumber = 5;
                $price = $depthBids[$buyDepthNumber];
                $res = $api->buy($ticker, $quantity, $price);
                if ($res['status'] == 'NEW' || $res['status'] == 'PARTIALLY_FILLED' || $res['status'] == 'FILLED') {
                    Redis::set('binance:buy:number_'.$pair.'4', $res['orderId']);
                    Redis::set('binance:buy:price_'.$pair.'4', $res['price']);
                    Redis::set('binance:buy:mark_'.$pair.'4', 1); //标记买单创建
                    // 设定此次挂单时间
                    $timeLimit = Redis::get(ConsoleService::BINANCE_RUN_TIME_LIMIT_VALUE);
                    if (is_null($timeLimit)) $timeLimit = 30;
                    Redis::setex(ConsoleService::BINANCE_RUN_TIME_LIMIT_KEY.'4', $timeLimit, '1');
                    return ['result' => true, 'message' => 'create buy order success '.json_encode($res)];
                } else {
                    return ['result' => false, 'message' => 'create buy order fail '.json_encode($res)];
                }
            } else {
                // 有完成的买单 则下卖单
                if (!is_null($buyNumber) && $buyStatus['side'] == 'BUY' && $buyStatus['status'] == 'FILLED') {
                    $sellDepthNumber = Redis::get('binance:sell:offset_'.$pair); //卖单偏移值
                    if (is_null($sellDepthNumber)) $sellDepthNumber = 0.2;
                    $sellPrice = Redis::get('binance:buy:price_'.$pair.'4') * (1+0.001) + $sellDepthNumber;
                    $sellPrice = number_format($sellPrice, 2, '.', '');
                    $res = $api->sell($ticker, $quantity, $sellPrice);
                    if (isset($res['msg'])) {
                        return ['result' => false, 'message' => $res['msg']];
                    }
                    if ($res['status'] == 'NEW' || $res['status'] == 'PARTIALLY_FILLED' || $res['status'] == 'FILLED') {
                        Redis::set('binance:sell:number_'.$pair.'4', $res['orderId']);
                        Redis::set('binance:buy:mark_'.$pair.'4', 2); //标记对应买单处理了
                        return ['result' => true, 'message' => 'create sell order success '.json_encode($res)];
                    } else {
                        return ['result' => false, 'message' => 'create sell order fail '.json_encode($res)];
                    }
                }
            }
        }
        return ['result' => false, 'message' => 'have no action'];
    }

    public static function cancelSellOrder($pair)
    {
        Log::debug('not init all order');
        $api = app('Binance');
        $ticker = implode('', explode('_', $pair));  // pair - ETH_USDT  ticker - EHTUSDT
        $sellNumber1 = Redis::get('binance:sell:number_' . $pair . '1');
        $sellStatus1 = $api->orderStatus($ticker, $sellNumber1);
        $sellNumber2 = Redis::get('binance:sell:number_' . $pair . '2');
        $sellStatus2 = $api->orderStatus($ticker, $sellNumber2);
        $sellNumber3 = Redis::get('binance:sell:number_' . $pair . '3');
        $sellStatus3 = $api->orderStatus($ticker, $sellNumber3);
        $sellNumber4 = Redis::get('binance:sell:number_' . $pair . '4');
        $sellStatus4 = $api->orderStatus($ticker, $sellNumber4);
        if (!is_null($sellNumber1) && $sellStatus1['status'] == 'NEW' && $sellStatus2['status'] == 'NEW' && $sellStatus3['status'] == 'NEW' && $sellStatus4['status'] == 'NEW') {
            // 四组卖单全未成交
            $status = Redis::get('switch_'.$pair);
            if (is_null($status) || $status == 2) {
                return false;
            } else {
//                Redis::flushdb();
                Redis::del("binance:buy:number_ETH_USDT1");
                Redis::del("binance:buy:price_ETH_USDT1");
                Redis::del("binance:sell:number_ETH_USDT1");
                Redis::del("binance:buy:mark_ETH_USDT1");

                Redis::del("binance:buy:number_ETH_USDT2");
                Redis::del("binance:buy:price_ETH_USDT2");
                Redis::del("binance:sell:number_ETH_USDT2");
                Redis::del("binance:buy:mark_ETH_USDT2");

                Redis::del("binance:buy:number_ETH_USDT3");
                Redis::del("binance:buy:price_ETH_USDT3");
                Redis::del("binance:sell:number_ETH_USDT3");
                Redis::del("binance:buy:mark_ETH_USDT3");

                Redis::del("binance:buy:number_ETH_USDT4");
                Redis::del("binance:buy:price_ETH_USDT4");
                Redis::del("binance:sell:number_ETH_USDT4");
                Redis::del("binance:buy:mark_ETH_USDT4");

                Log::debug('init all order');
                return true;
            }
        }
    }
}