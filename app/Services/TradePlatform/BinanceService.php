<?php

namespace App\Services\TradePlatform;

use Illuminate\Support\Facades\Redis;

class BinanceService
{
    const PRICE_DEPTH_NO = 50;
    const TRADE_FEE = 0; // binance
    const PROFIT_COIN1_PERCENT = 0.0005; // 创建订单时盈利比例

    public static function getBalanceAvail($pair)
    {
        $api = app('Binance');
        $balance = $api->balances();
        $coins = explode('_', $pair);
        return [
            'coin1_total' => $balance[$coins[0]]['available'] + $balance[$coins[0]]['onOrder'],
            'coin2_total' => $balance[$coins[1]]['available'] + $balance[$coins[1]]['onOrder'],
            'coin1_avail' => $balance[$coins[0]]['available'],
            'coin2_avail' => $balance[$coins[1]]['available']
        ];
    }

    public static function getPrice($pair, $ticker, $pairBalance, $lastPrice)
    {
        $api = app('Binance');
        $depth = $api->depth($ticker);
        // 卖单从低到高 买单从高到低排序
//        array_multisort(array_column($depth['asks'],0),SORT_ASC,$depth['asks']);
//        dd($depth);
        $coin1Total = $pairBalance['coin1_avail'] * 1;  // 满仓
        $coin2Total = $pairBalance['coin2_avail'] * 1;  // 满仓

        // 测试
        $coin1Total = 1;
        $coin2Total = 880;

//        $currCoin1 = $coin1Total + $coin2Total / $lastPrice;
        $currCoin2 = $coin1Total * $lastPrice + $coin2Total;
        // 计算买卖价
        $priceDepth = self::PRICE_DEPTH_NO;
        $coin1Percent = Redis::get('coin1_percent:'.$pair);
        if (is_null($coin1Percent)) $coin1Percent = self::PROFIT_COIN1_PERCENT;
//        $amountBids = 0;
//        $amountAsks = 0;
        $depthBids = array_keys($depth['bids']);
        $depthAsks = array_keys($depth['asks']);
        for ($i = 0; $i < $priceDepth; $i++) {
            if (!isset($depthBids[$i]) || !isset($depthAsks[$i])) break;
//            $amountBids += $depth['asks'][$i][1];
//            $amountAsks += $depth['bids'][$i][1];
            $sellPrice = $depthAsks[$i];
            $buyPrice = $depthBids[$i];
//            $resCoin1 = $sellPrice*$coin1Total*(1-self::TRADE_FEE)/$lastPrice + $coin2Total/$buyPrice*(1-self::TRADE_FEE);
            $resCoin2 = $sellPrice*$coin1Total*(1-self::TRADE_FEE) + $coin2Total/$buyPrice*(1-self::TRADE_FEE) * $lastPrice;

            $getCoin1 = $coin2Total/$buyPrice;
            $getCoin2 = $sellPrice*$coin1Total;
//            echo $resCoin1-$currCoin1 .' ' . $amountAsks . ' '. $amountBids . '<br>';
            echo $resCoin2-$currCoin2 .' ' . $sellPrice . ' '. $buyPrice . '<br>';
            if ($resCoin2 > $currCoin2 * (1 + $coin1Percent)) {
                dd(($resCoin2-$currCoin2)/$currCoin2. ' ' . $getCoin1 . ' '. $getCoin2 . $i);
                return [
                    'sell_price' => $sellPrice,
                    'buy_price' => $buyPrice,
                ];
            }
        }

        return null;
    }
}