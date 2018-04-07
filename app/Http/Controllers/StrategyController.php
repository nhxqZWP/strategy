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
          dd('gate.io交易手续费0.2%');
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

     public function updateRunStatusNew(Request $request)
     {
          $pair = $request->get('pair');
          $status = $request->get('status');
          Redis::set('switch_new_' . $pair, $status);
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
            Redis::set('binance:buy:mark_'.$pair.'1', 2); //只有1的
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

    public function postQuantity(Request $request)
    {
        $quantity = $request->get('quantity', 0.1);
        $pair = $request->get('pair');
        Redis::set('binance:buy:quantity_'.$pair, $quantity);
        return redirect()->back()->with('message', '修改成功');
    }

    public function postParams(Request $request)
    {
        $input = $request->input();
        $pair = $input['pair'];
        $quantity = Redis::get('binance:buy:quantity_'.$pair.'1'); //买卖单数量
        if (is_null($quantity)) $quantity = 0.02;  // 买卖1个eth
        Redis::set('binance:buy:quantity_'.$pair.'1', $input['group1_coin1']);
        Redis::set('binance:buy:quantity_'.$pair.'2', $input['group2_coin1']);
        Redis::set('binance:buy:quantity_'.$pair.'3', $input['group3_coin1']);
        Redis::set('binance:buy:quantity_'.$pair.'4', $input['group4_coin1']);
        Redis::set('binance:buy:offset_'.$pair.'1', $input['group1_offset']);
        Redis::set('binance:buy:offset_'.$pair.'2', $input['group2_offset']);
        Redis::set('binance:buy:offset_'.$pair.'3', $input['group3_offset']);
        Redis::set('binance:buy:offset_'.$pair.'4', $input['group4_offset']);
        return redirect()->back()->with('message', '修改成功');
    }

     public function postParamsNew(Request $request)
     {
          $input = $request->input();
          $pair = $input['pair'];
          $quantity = Redis::get('binance:buy:quantity_'.$pair.'new'); //买卖单数量
          if (is_null($quantity)) $quantity = 0.02;  // 买卖1个eth
          Redis::set('binance:buy:quantity_'.$pair.'new', $input['group_coin']);
          Redis::set('binance:buy:offset_'.$pair.'new', $input['group_offset']);
          return redirect()->back()->with('message', '修改成功');
     }

    public function postProfit(Request $request)
    {
        $pair = $request->get('pair', 'ETH_USDT');
        $profit = $request->get('profit', 0.2);
        Redis::set('binance:sell:offset_'.$pair, $profit);
        return redirect()->back()->with('message', '修改成功');
    }

    public function postCancelSell(Request $request)
    {
        $time = $request->get('time', 6);
        Redis::set('binance:sell:cancel_limit_time', $time);
        return redirect()->back()->with('message', '修改成功');
    }

    public function getInit(Request $request)
    {
        $plat = $request->get('plat','binance');
        $pair = $request->get('pair');
        if ($plat == 'binance') {
            Redis::flushdb();
        }
        return redirect()->back()->with('message', '初始化成功');
    }

    public function stopLossOffset(Request $request)
    {
         $stopLossOffset = $request->get('stop_loss', 0);
         $pair = $request->get('pair', 'ETH_USDT');
         Redis::set('binance:sell:stop_loss_offset_'.$pair.'new', $stopLossOffset);
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
        $bnb = $wallet['BNB'];
        // 当前币价
        $lastPrice = $api->prices()[$ticker];
        // usdt_cny
        $usdtCny = GateIo::get_ticker('usdt_cny');
        // 最长挂单时间
        $timeLimit = Redis::get(ConsoleService::BINANCE_RUN_TIME_LIMIT_VALUE);
        if (is_null($timeLimit)) $timeLimit = 30;
        // 买卖单数量
        $quantity = Redis::get('binance:buy:quantity_'.$pair); //买卖单数量
        if (is_null($quantity)) $quantity = 0.1;  // 买卖1个eth
        // 每笔利润率
        $profit = Redis::get('binance:sell:offset_'.$pair);
        if (is_null($profit)) $profit = 0.2;
        // params
        $param['1coin'] = Redis::get('binance:buy:quantity_'.$pair.'1');
        $param['2coin'] = Redis::get('binance:buy:quantity_'.$pair.'2');
        $param['3coin'] = Redis::get('binance:buy:quantity_'.$pair.'3');
        $param['4coin'] = Redis::get('binance:buy:quantity_'.$pair.'4');
        $param['1offset'] = Redis::get('binance:buy:offset_'.$pair.'1');
        $param['2offset'] = Redis::get('binance:buy:offset_'.$pair.'2');
        $param['3offset'] = Redis::get('binance:buy:offset_'.$pair.'3');
        $param['4offset'] = Redis::get('binance:buy:offset_'.$pair.'4');
        // 卖单自动取消时间
        $sellCancel = Redis::get('binance:sell:cancel_limit_time');
        if (is_null($sellCancel)) $sellCancel = 6;
        $data = [
            'openOrders' => $openOrders,
            'tradeHistory' => $tradeHistory,
            'open' => $open,
            'pair' => $pair,
            'coin1' => $coin1,
            'coin2' => $coin2,
            'timeLimit' => $timeLimit,
            'quantity' => $quantity,
            'param' => $param,
            'profit' => $profit,
            'sellCancelTime' => $sellCancel,
            'lastPrice' => $lastPrice,
            'usdtCny' => $usdtCny['last'],
            'bnb' => $bnb
        ];
        return view('binance.coin_analysis_show', $data);
    }

    // 追涨杀跌 下止损单
     public function getBinanceOneCoinNew(Request $request)
     {
          $pair = $request->get('pair');
          if (empty($pair)) return redirect()->back()->withInput()->with('error', '没有选择交易对');

          // 运行状态
          $open = Redis::get('switch_new_' . $pair);
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
          $bnb = $wallet['BNB'];
          // 当前币价
          $lastPrice = $api->prices()[$ticker];
          // usdt_cny
          $usdtCny = GateIo::get_ticker('usdt_cny');
          // 最长挂买单时间
          $timeLimit = Redis::get(ConsoleService::BINANCE_RUN_TIME_LIMIT_VALUE);
          if (is_null($timeLimit)) $timeLimit = 30;
          // 买卖单数量
          $quantity = Redis::get('binance:buy:quantity_'.$pair.'new'); //买卖单数量
          if (is_null($quantity)) $quantity = 0.1;  // 买卖1个eth
          // 每笔利润率
          $profit = Redis::get('binance:sell:offset_'.$pair.'new');
          if (is_null($profit)) $profit = 0.2;
          // 止损偏移
          $stopLossOffset = Redis::get('binance:sell:stop_loss_offset_'.$pair.'new');
          // params
          $param['coin'] = Redis::get('binance:buy:quantity_'.$pair.'new');
//          $param['1coin'] = Redis::get('binance:buy:quantity_'.$pair.'1');
//          $param['2coin'] = Redis::get('binance:buy:quantity_'.$pair.'2');
//          $param['3coin'] = Redis::get('binance:buy:quantity_'.$pair.'3');
//          $param['4coin'] = Redis::get('binance:buy:quantity_'.$pair.'4');
          $param['offset'] = Redis::get('binance:buy:offset_'.$pair.'new');
//          $param['1offset'] = Redis::get('binance:buy:offset_'.$pair.'1');
//          $param['2offset'] = Redis::get('binance:buy:offset_'.$pair.'2');
//          $param['3offset'] = Redis::get('binance:buy:offset_'.$pair.'3');
//          $param['4offset'] = Redis::get('binance:buy:offset_'.$pair.'4');
          // 卖单自动取消时间
//          $sellCancel = Redis::get('binance:sell:cancel_limit_time');
//          if (is_null($sellCancel)) $sellCancel = 6;
          $data = [
               'openOrders' => $openOrders,
               'tradeHistory' => $tradeHistory,
               'open' => $open,
               'pair' => $pair,
               'coin1' => $coin1,
               'coin2' => $coin2,
               'timeLimit' => $timeLimit,
               'quantity' => $quantity,
               'param' => $param,
               'profit' => $profit,
//               'sellCancelTime' => $sellCancel,
               'lastPrice' => $lastPrice,
               'usdtCny' => $usdtCny['last'],
               'bnb' => $bnb,
               'stopLoss' => $stopLossOffset
          ];
          return view('binance.coin_analysis_show_new', $data);
     }

     // 五日线法
     public function getBinanceOneCoinNew2(Request $request)
     {
          $pair = $request->get('pair');
          if (empty($pair)) return redirect()->back()->withInput()->with('error', '没有选择交易对');

          // 运行状态
          $open = Redis::get('switch_5day_' . $pair);
          if (is_null($open) || $open == 2) $open = 2;
          $api = app('Binance');
          $ticker = implode('', explode('_', $pair));  // pair - ETH_USDT  ticker - EHTUSDT


          // test show k线图
          $ticks = $api->candlesticks($ticker, "5m");
          $ends = array_slice($ticks,-6,5);
          $sumFive = 0;
          foreach ($ends as $e) {
               $sumFive += $e['close'];
          }
          $fiveAvePrice = $sumFive / 5;
          dd($fiveAvePrice);

//          dd($endSecond[0]);
//          dd($endSecond[0]['close'] - $endSecond[0]['open']);
          $data = [];
          foreach ($ticks as $k => $t) {
               $k = date('Y-m-d H:i:s', $k/1000);
               $data[$k] = $t;
          }
          krsort($ticks);
          dd($ticks);



          // 获取当前挂单列表
          $openOrders = $api->openOrders($ticker);
          // 获取24内成交记录
          $tradeHistory = array_reverse($api->history($ticker, 20));
          // 此交易对钱包余额
          $wallet = $api->balances();
          $coin1 = $wallet[explode('_',$pair)[0]];
          $coin2 = $wallet[explode('_',$pair)[1]];
          $bnb = $wallet['BNB'];
          // 当前币价
          $lastPrice = $api->prices()[$ticker];
          // usdt_cny
          $usdtCny = GateIo::get_ticker('usdt_cny');
          // 最长挂买单时间
          $timeLimit = Redis::get(ConsoleService::BINANCE_RUN_TIME_LIMIT_VALUE);
          if (is_null($timeLimit)) $timeLimit = 30;
          // 买卖单数量
          $quantity = Redis::get('binance:buy:quantity_'.$pair.'new'); //买卖单数量
          if (is_null($quantity)) $quantity = 0.1;  // 买卖1个eth
          // 每笔利润率
          $profit = Redis::get('binance:sell:offset_'.$pair.'new');
          if (is_null($profit)) $profit = 0.2;
          // 止损偏移
          $stopLossOffset = Redis::get('binance:sell:stop_loss_offset_'.$pair.'new');
          // params
          $param['coin'] = Redis::get('binance:buy:quantity_'.$pair.'new');
//          $param['1coin'] = Redis::get('binance:buy:quantity_'.$pair.'1');
//          $param['2coin'] = Redis::get('binance:buy:quantity_'.$pair.'2');
//          $param['3coin'] = Redis::get('binance:buy:quantity_'.$pair.'3');
//          $param['4coin'] = Redis::get('binance:buy:quantity_'.$pair.'4');
          $param['offset'] = Redis::get('binance:buy:offset_'.$pair.'new');
//          $param['1offset'] = Redis::get('binance:buy:offset_'.$pair.'1');
//          $param['2offset'] = Redis::get('binance:buy:offset_'.$pair.'2');
//          $param['3offset'] = Redis::get('binance:buy:offset_'.$pair.'3');
//          $param['4offset'] = Redis::get('binance:buy:offset_'.$pair.'4');
          // 卖单自动取消时间
//          $sellCancel = Redis::get('binance:sell:cancel_limit_time');
//          if (is_null($sellCancel)) $sellCancel = 6;
          $data = [
               'openOrders' => $openOrders,
               'tradeHistory' => $tradeHistory,
               'open' => $open,
               'pair' => $pair,
               'coin1' => $coin1,
               'coin2' => $coin2,
               'timeLimit' => $timeLimit,
               'quantity' => $quantity,
               'param' => $param,
               'profit' => $profit,
//               'sellCancelTime' => $sellCancel,
               'lastPrice' => $lastPrice,
               'usdtCny' => $usdtCny['last'],
               'bnb' => $bnb,
               'stopLoss' => $stopLossOffset
          ];
          return view('binance.coin_analysis_show_new', $data);

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