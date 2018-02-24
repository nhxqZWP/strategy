<?php

namespace App\Http\Controllers;

use App\Services\ConsoleService;
use App\Services\TradePlatform\GateIo;
use App\Services\TradePlatform\GateIoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class StrategyController extends Controller
{
    public function getGateIoOneCoin(Request $request)
    {
        $pair = $request->get('pair');
        if (empty($pair)) return redirect()->back()->withInput()->with('error', '没有选择交易对');

        // 运行状态
        $open = Redis::get('switch_' . $pair);
        if (is_null($open) || $open == 2) $open = 2;
        // 获取当前挂单列表
        $openOrders = GateIo::open_orders($pair);
        // 获取24内成交记录
        $tradeHistory = GateIo::get_my_trade_history_all($pair);
        // 此交易对钱包余额
        $wallet = GateIoService::getPairBalance($pair);
        // 最长挂单时间
        $timeLimit = Redis::get(ConsoleService::GTC_RUN_TIME_LIMIT_VALUE);
        if (is_null($timeLimit)) $timeLimit = 60 * 20;
        // 每笔利润率
        $coin1Percent = Redis::get('coin1_percent:'.$pair);
        if (is_null($coin1Percent)) $coin1Percent = 0.02;
        // 最新交易价格
        $lastPrice = GateIo::get_ticker($pair)['last'];
        $walletTotal = $wallet['coin2_total'] + $wallet['coin1_total'] * $lastPrice;
        $data = [
            'openOrders' => $openOrders,
            'tradeHistory' => $tradeHistory,
            'open' => $open,
            'pair' => $pair,
            'wallet' => $wallet,
            'timeLimit' => $timeLimit,
            'coinPercent' => $coin1Percent,
            'walletTotal' => $walletTotal,
            'lastPrice' => $lastPrice
        ];
        return view('coin_analysis_show', $data);
    }

    public function updateRunStatus(Request $request)
    {
        $pair = $request->get('pair');
        $status = $request->get('status');
        Redis::set('switch_' . $pair, $status);
        return redirect()->back();
    }

    public function cancelOneOrder(Request $request)
    {
        $pair = $request->get('pair');
        $number = $request->get('number');
        $plat = $request->get('plat','binance');
        $ticker = implode('', explode('_', $pair));  // pair - ETH_USDT  ticker - EHTUSDT
        if ($plat == 'binance') {
            $api = app('Binance');
            $api->cancel($ticker, $number);
            Redis::set('binance:buy:mark_'.$pair.'1', 2);
        }
        return redirect()->back();
    }

    public function timeLimit(Request $request)
    {
        $limit = $request->get('limit', 30);
        $plat = $request->get('plat','binance');
        if ($plat == 'binance') {
            Redis::set(ConsoleService::BINANCE_RUN_TIME_LIMIT_VALUE, $limit);
        } else {
            Redis::set(ConsoleService::GTC_RUN_TIME_LIMIT_VALUE, $limit);
        }
        return redirect()->back()->with('message', '修改成功');
    }

    public function getpercent(Request $request)
    {
        $percent = $request->get('percent', 0.02);
        $pair = $request->get('pair');
        Redis::set('coin1_percent:'.$pair, $percent);
        return redirect()->back()->with('message', '修改成功');
    }

    public function getBinanceOneCoin(Request $request)
    {
        $pair = $request->get('pair');
        if (empty($pair)) return redirect()->back()->withInput()->with('error', '没有选择交易对');

        // 运行状态
        $open = Redis::get('switch_' . $pair);
        if (is_null($open) || $open == 2) $open = 2;
        $api = app('Binance');
        $ticker = implode('', explode('_', $pair));  // pair - ETH_USDT  ticker - EHTUSDT
        // 获取当前挂单列表
        $openOrders = $api->openOrders($ticker);
        // 获取24内成交记录
        $tradeHistory = array_reverse($api->history($ticker, 20));
        // 此交易对钱包余额
        $wallet = $api->balances();
        $coin1 = $wallet[explode('_',$pair)[0]];
        $coin2 = $wallet[explode('_',$pair)[1]];
        // 最长挂单时间
        $timeLimit = Redis::get(ConsoleService::BINANCE_RUN_TIME_LIMIT_VALUE);
        if (is_null($timeLimit)) $timeLimit = 30;
        // 每笔利润率
//        $coin1Percent = Redis::get('coin1_percent:'.$pair);
//        if (is_null($coin1Percent)) $coin1Percent = 0.02;
        $data = [
            'openOrders' => $openOrders,
            'tradeHistory' => $tradeHistory,
            'open' => $open,
            'pair' => $pair,
            'coin1' => $coin1,
            'coin2' => $coin2,
            'timeLimit' => $timeLimit,
//            'coinPercent' => $coin1Percent,
        ];
        return view('binance.coin_analysis_show', $data);
    }

//    public function chart()
//    {
//        $product = Product::where('id', $pid)->first(['out_start', 'out_end'])->toArray();
//        $days = [];
//
//        if (Helper::isDefaultDatetime($product['out_end'])) {
//            $end = time();
//            $start = strtotime('-14 day', $end);
//        } else {
//            $end = strtotime($product['out_end']);
//            $start = strtotime($product['out_start']);
//        }
//        $t = $start;
//        //$t = Helper::isDefaultDatetime($product['out_start']) ? time() : strtotime($product['out_start']);
//        //$end = Helper::isDefaultDatetime($product['out_end']) ? strtotime('+7 day', $t) : strtotime($product['out_end']);
//        while ($t < $end) {
//            $days[date('m-d', $t)] = 0;
//            $t += 86400;
//        }
//        $days[date('m-d', $t)] = 0;
//
//        DB::table('orders')->where(['pid' => $pid, 'type' => 1, 'status' => 1])
//            ->where('updated_at', '>=', date('Y-m-d 00:00:00', $start))
//            ->where('updated_at', '<', date('Y-m-d 23:59:59', $end))
//            ->chunk(500, function ($orders) use (&$days) {
//                foreach ($orders as $o) {
//                    $d = date('m-d', strtotime($o->updated_at));
//                    $days[$d] += $o->quantity;
//                }
//            });
//
//        return $days;
//        $days = ProductService::statBuyByDay($pid);
//
//        $data = [
//            'x_axis' => json_encode(array_keys($days)),
//            'y_axis' => json_encode(array_values($days)),
//        ];
//    }

}