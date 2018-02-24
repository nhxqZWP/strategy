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
            Log::debug($res['message']);
//        }
    }
}