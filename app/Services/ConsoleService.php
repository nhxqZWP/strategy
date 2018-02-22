<?php

namespace App\Services;

use App\Services\HighFreqStrategy\BuyGoUpService;
use App\Services\TradePlatform\GateIoService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ConsoleService
{
    const GTC_RUN_TIME_LIMIT_KEY = 'gtc_run_time_limit_key';
    const GTC_RUN_TIME_LIMIT_VALUE = 'gtc_run_time_limit_value';

    public static function runShotLine()
    {
        $pair = 'gtc_usdt';
        // 运行总开关
        $open = Redis::get('switch_'.$pair);
        if (is_null($open) || $open == 2) return false;
        // 如果挂单都成交或者到了指定时间
        $openOrderExist = GateIoService::getOpenOrdersExist($pair);
        $runTimeLimit = Redis::get(self::GTC_RUN_TIME_LIMIT_KEY);
        if (!$openOrderExist || is_null($runTimeLimit)) {
            $res = BuyGoUpService::GateIoShotLineRobot($pair);
            if (!$res['result']) {
                Log::debug($res['message']);
            }
        }
    }
}