<?php

namespace App\Services\TradePlatform;

use Illuminate\Support\Facades\Redis;

class GateIoService
{
    private static $trxUsdt = 'strategy:coin_to_coin:trx_usdt';
    const PRICE_DEPTH_NO = 75; // gate.io 100
    const TRADE_FEE = 0.002; // gate.io
    const PROFIT_COIN1_PERCENT = 0.02; // 创建订单时盈利比例

    /**
     * 获取订单参数  [ decimal_places  min_amount  fee ]
     * @param string $pair
     * @return array|null
     */
    public static function getOrderParams($pair = '')
    {
        if (empty($pair)) return GateIo::get_marketinfo();
        $data = GateIo::get_marketinfo();
        $res = [];
        foreach ($data['pairs'] as $k=>$d) {
            if (array_keys($d)[0] == $pair) $res = $d;
        }
        return $res;
    }

    /**
     * 获取某一交易对余额数量(包括冻结的)
     * @param $pair
     * @return null
     */
    public static function getPairBalance($pair)
    {
        $coins = explode('_', $pair);
        $coins[0] = strtoupper($coins[0]);
        $coins[1] = strtoupper($coins[1]);
        $balance = GateIo::get_balances();
        if (isset($balance['available']) && isset($balance['locked'])) {
            $coin1Avai = isset($balance['available'][$coins[0]]) ? $balance['available'][$coins[0]] : 0;
            $coin1Lock = isset($balance['locked'][$coins[0]]) ? $balance['locked'][$coins[0]] : 0;
            $coin2Avai = isset($balance['available'][$coins[1]]) ? $balance['available'][$coins[1]] : 0;
            $coin2Lock = isset($balance['locked'][$coins[1]]) ? $balance['locked'][$coins[1]] : 0;
            return [
                'coin1_total' => $coin1Avai + $coin1Lock,
                'coin2_total' => $coin2Avai + $coin2Lock,
                'coin1_avail' => $coin1Avai,
                'coin2_avail' => $coin2Avai
            ];
        } elseif (isset($balance['available'])) {
            $coin1Avai = isset($balance['available'][$coins[0]]) ? $balance['available'][$coins[0]] : 0;
            $coin2Avai = isset($balance['available'][$coins[1]]) ? $balance['available'][$coins[1]] : 0;
            return [
                'coin1_total' => $coin1Avai,
                'coin2_total' => $coin2Avai,
                'coin1_avail' => $coin1Avai,
                'coin2_avail' => $coin2Avai
            ];
        } elseif (isset($balance['locked'])) {
            $coin1Lock = isset($balance['locked'][$coins[0]]) ? $balance['locked'][$coins[0]] : 0;
            $coin2Lock = isset($balance['locked'][$coins[1]]) ? $balance['locked'][$coins[1]] : 0;
            return [
                'coin1_total' => $coin1Lock,
                'coin2_total' => $coin2Lock,
                'coin1_avail' => $coin1Lock,
                'coin2_avail' => $coin2Lock
            ];
        } else {
            return null;
        }
    }

    /**
     * 获取某一交易对可用余额
     * @param $pair
     * @return array|null
     */
    public static function getBalanceAvail($pair)
    {
        $coins = explode('_', $pair);
        $coins[0] = strtoupper($coins[0]);
        $coins[1] = strtoupper($coins[1]);
        $balance = GateIo::get_balances();
        if (isset($balance['available'])) {
            $coin1Avail = isset($balance['available'][$coins[0]]) ? $balance['available'][$coins[0]] : 0;
            $coin2Avail = isset($balance['available'][$coins[1]]) ? $balance['available'][$coins[1]] : 0;
            return [$coin1Avail, $coin2Avail];
        } else {
            return null;
        }
    }

    /**
     * 获取下单价格
     * @param $pair
     * @param $pairBalance
     * @param $lastPrice
     * @return array|null
     */
    public static function getPrice($pair, $pairBalance, $lastPrice)
    {
        $depth = GateIo::get_orderbook($pair);
        // 卖单从低到高 买单从高到低排序
        array_multisort(array_column($depth['asks'],0),SORT_ASC,$depth['asks']);
//        dd($depth);
        $coin1Total = $pairBalance['coin1_total'] * 1;  // 满仓
        $coin2Total = $pairBalance['coin2_total'] * 1;  // 满仓
        $currCoin1 = $coin1Total + $coin2Total / $lastPrice;
        // 计算买卖价
        $priceDepth = self::PRICE_DEPTH_NO;
        $coin1Percent = Redis::get('coin1_percent:'.$pair);
        if (is_null($coin1Percent)) $coin1Percent = 0.02;
//        $amountBids = 0;
//        $amountAsks = 0;
        for ($i = 0; $i < $priceDepth; $i++) {
            if (!isset($depth['asks'][$i]) || !isset($depth['bids'][$i])) break;
//            $amountBids += $depth['asks'][$i][1];
//            $amountAsks += $depth['bids'][$i][1];
            $sellPrice = $depth['asks'][$i][0];
            $buyPrice = $depth['bids'][$i][0];
            $resCoin1 = $sellPrice*$coin1Total*(1-self::TRADE_FEE)/$lastPrice + $coin2Total/$buyPrice*(1-self::TRADE_FEE);
//            echo $resCoin1-$currCoin1 .' ' . $amountAsks . ' '. $amountBids . '<br>';
//            echo $resCoin1-$currCoin1 .' ' . $sellPrice . ' '. $buyPrice . '<br>';
            if ($resCoin1 > $currCoin1 * (1 + $coin1Percent)) {
//                dd($resCoin1. ' '.$currCoin1. ' ' . $i );
                return [
                    'sell_price' => $sellPrice,
                    'buy_price' => $buyPrice,
                ];
            }
        }
        return null;
    }

    public static function getOpenOrdersExist($pair)
    {
        $openOrders = GateIo::open_orders($pair);
        if (empty($openOrders['orders'])) {
            return false;
        } else {
            return true;
        }
    }
}