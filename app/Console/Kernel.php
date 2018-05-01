<?php

namespace App\Console;

use App\Services\ConsoleService;
use App\Services\HighFreqStrategy\ShotLineService;
use App\Services\TradePlatform\HuobiService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\Inspire::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
//        ini_set('memory_limit', '500M'); //内存限制
//        $schedule->call(function () {
//            for ($i = 0; $i < 11; $i++) {
//                ConsoleService::runShotLine();
//                sleep(5);
//            }
//        })->cron('* * * * *');
//
//        $schedule->call(function () {
//            for ($i = 0; $i < 11; $i++) {
//                sleep(1);
//                ConsoleService::runShotLine2();
//                sleep(4);
//            }
//        })->cron('* * * * *');
//
//        $schedule->call(function () {
//            for ($i = 0; $i < 11; $i++) {
//                sleep(2);
//                ConsoleService::runShotLine3();
//                sleep(3);
//            }
//        })->cron('* * * * *');
//
//        $schedule->call(function () {
//            for ($i = 0; $i < 11; $i++) {
//                sleep(3);
//                ConsoleService::runShotLine4();
//                sleep(2);
//            }
//        })->cron('* * * * *');
//
//         $schedule->call(function () {
//              for ($i = 0; $i < 10; $i++) {
//                   ConsoleService::runShotLineNew();
//                   sleep(5);
//              }
//         })->cron('* * * * *');

//         $schedule->call(function () {
//              ConsoleService::KlineToChange('1m', 'ETHUSDT');
//         })->cron('* * * * *');

//         $schedule->call(function () {
//              for ($i = 0; $i < 10; $i++) {
//                   ConsoleService::KlineMA5('BTCUSDT');
//                   sleep(5);
//              }
//         })->cron('* * * * *');

//        $limitTime = Redis::get('binance:sell:cancel_limit_time');
//        if (is_null($limitTime)) $limitTime = 6;
//        $schedule->call(function () {
//                ShotLineService::cancelSellOrder('ETH_USDT');
//        })->cron('0 */'.$limitTime.' * * *');
        // everyTenMinutes everyThirtyMinutes hourly

         $schedule->call(function () {
              HuobiService::putAllDepth();
              Log::debug('run depth analysis');
         })->cron('* * * * *');
    }

}
