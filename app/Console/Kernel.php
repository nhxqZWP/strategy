<?php

namespace App\Console;

use App\Services\ConsoleService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

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
        $schedule->call(function () {
            for ($i = 0; $i < 12; $i++) {
                ConsoleService::runShotLine();
                sleep(5);
            }
        })->cron('* * * * *');

        $schedule->call(function () {
            for ($i = 0; $i < 12; $i++) {
                sleep(1);
                ConsoleService::runShotLine2();
                sleep(4);
            }
        })->cron('* * * * *');

        $schedule->call(function () {
            for ($i = 0; $i < 12; $i++) {
                sleep(2);
                ConsoleService::runShotLine3();
                sleep(3);
            }
        })->cron('* * * * *');

        $schedule->call(function () {
            for ($i = 0; $i < 12; $i++) {
                sleep(3);
                ConsoleService::runShotLine4();
                sleep(2);
            }
        })->cron('* * * * *');
        // everyTenMinutes everyThirtyMinutes hourly
    }
}
