<?php

namespace App\Services;

use App\Services\HighFreqStrategy\BuyGoUpService;
use App\Services\HighFreqStrategy\ShotLineService;
use App\Services\TradePlatform\GateIoService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ConsoleService
{
    const GTC_RUN_TIME_LIMIT_KEY = 'gtc_run_time_limit_key';
    const GTC_RUN_TIME_LIMIT_VALUE = 'gtc_run_time_limit_value';

    const BINANCE_RUN_TIME_LIMIT_KEY = 'binance_run_time_limit_key';
    const BINANCE_RUN_TIME_LIMIT_VALUE = 'binance_run_time_limit_value';

//    public static function runShotLine()
//    {
//        $pair = 'gtc_usdt';
//        // 运行总开关
//        $open = Redis::get('switch_'.$pair);
//        if (is_null($open) || $open == 2) return false;
        // 如果挂单都成交或者到了指定时间
//        $openOrderExist = GateIoService::getOpenOrdersExist($pair);
//        $runTimeLimit = Redis::get(self::GTC_RUN_TIME_LIMIT_KEY);
//        if (!$openOrderExist || is_null($runTimeLimit)) {
//        if (!$openOrderExist) {
//            $res = BuyGoUpService::GateIoShotLineRobot($pair);
//            if (!$res['result']) {
//                Log::debug($res['message']);
//            }
//        }
//    }

    public static function runShotLine()
    {
        $pair = 'ETH_USDT';
        // 运行总开关
        $open = Redis::get('switch_'.$pair);
        if (is_null($open) || $open == 2) return false;
        // 如果挂单都成交或者到了指定时间
//        $runTimeLimit = Redis::get(self::GTC_RUN_TIME_LIMIT_KEY);
//        if (!$openOrderExist || is_null($runTimeLimit)) {
        $res = ShotLineService::BinanceShotLine2($pair);
//        if (!$res['result']) {
            Log::debug('1:'.$res['message']);
//        }
    }

    public static function runShotLine2()
    {
        $pair = 'ETH_USDT';
        // 运行总开关
        $open = Redis::get('switch_'.$pair);
        if (is_null($open) || $open == 2) return false;
        // 如果挂单都成交或者到了指定时间
//        $runTimeLimit = Redis::get(self::GTC_RUN_TIME_LIMIT_KEY);
//        if (!$openOrderExist || is_null($runTimeLimit)) {
        $res = ShotLineService::BinanceShotLine2Two($pair);
//        if (!$res['result']) {
        Log::debug('2:'.$res['message']);
//        }
    }

    public static function runShotLine3()
    {
        $pair = 'ETH_USDT';
        // 运行总开关
        $open = Redis::get('switch_'.$pair);
        if (is_null($open) || $open == 2) return false;
        // 如果挂单都成交或者到了指定时间
//        $runTimeLimit = Redis::get(self::GTC_RUN_TIME_LIMIT_KEY);
//        if (!$openOrderExist || is_null($runTimeLimit)) {
        $res = ShotLineService::BinanceShotLine2Three($pair);
//        if (!$res['result']) {
        Log::debug('3:'.$res['message']);
//        }
    }

    public static function runShotLine4()
    {
        $pair = 'ETH_USDT';
        // 运行总开关
        $open = Redis::get('switch_'.$pair);
        if (is_null($open) || $open == 2) return false;
        // 如果挂单都成交或者到了指定时间
//        $runTimeLimit = Redis::get(self::GTC_RUN_TIME_LIMIT_KEY);
//        if (!$openOrderExist || is_null($runTimeLimit)) {
        $res = ShotLineService::BinanceShotLine2Four($pair);
//        if (!$res['result']) {
        Log::debug('4:'.$res['message']);
//        }
    }

     public static function runShotLineNew()
     {
          $pair = 'ETH_USDT';
          // 运行总开关
          $open = Redis::get('switch_new_'.$pair);
          if (is_null($open) || $open == 2) return false;
          // 每天日志开关
          $everyDaySwitch = Redis::get('switch_new_log'.$pair);
          if (time() > strtotime(date('Y-m-d 23:56:00')) && $everyDaySwitch == 2) {
               // 记账
               $api = app('Binance');
               $wallet = $api->balances();
               $coin1 = $wallet[explode('_',$pair)[0]];
               $coin2 = $wallet[explode('_',$pair)[1]];
               \DB::table('everyday_profit')->insert(
                    [
                         'uid' => 1, //zwp
                         'coin_name' => explode('_',$pair)[0],
                         'coin_avail' => $coin1['available'],
                         'coin_onorder' => $coin1['onOrder'],
                         'usdt_avail' => $coin2['available'],
                         'usdt_onorder' => $coin2['onOrder'],
                         'trade_no' => strtotime(date('Y-m-d 00:00:00'))
                    ]
               );
               Redis::set('switch_new_log'.$pair, 1);
          }
          if ($everyDaySwitch == 2) {
               return null;
          }
          // 如果挂单都成交或者到了指定时间
//        $runTimeLimit = Redis::get(self::GTC_RUN_TIME_LIMIT_KEY);
//        if (!$openOrderExist || is_null($runTimeLimit)) {
          $res = ShotLineService::BinanceShotLineNew($pair);
//        if (!$res['result']) {
          Log::debug('new:'.$res['message']);
//        }
     }

     public static function KlineToChange($period = '1m', $ticker = 'BTCUSDT')
     {
          $api = app('Binance');
          $ticks = $api->candlesticks($ticker, $period); //ethusdt
          // 记录价格趋势
//         $end = end($ticks);
//         $change = $end['close'] - $end['open'];
          $endSecond = array_slice($ticks,-2,1);
          $change = $endSecond[0]['close'] - $endSecond[0]['open'];
          if ($change > 0) {
               Redis::set('binance:price_change', 1);  //1-涨 2-跌
               return 1;
          } elseif ($change < 0) {
               Redis::set('binance:price_change', 2);  //1-涨 2-跌
               return 2;
          } else {
               return null;
          }
     }

     public static function KlineMA5($ticker = 'BTCUSDT')
     {
          $api = app('Binance');
          $ticks = $api->candlesticks($ticker, "5m");
          $ends = array_slice($ticks,-6,5);
          $sumFive = 0;
          foreach ($ends as $e) {
               $sumFive += $e['close'];
          }
          $fiveAvePrice = $sumFive / 5;
          Redis::set('klineMa5m_'.$ticker, $fiveAvePrice);
     }
}