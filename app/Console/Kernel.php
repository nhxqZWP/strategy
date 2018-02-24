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
            for ($i = 0; $i < 10; $i++) {
                sleep(6);
                ConsoleService::runShotLine2();
            }
        })->cron('* * * * *');
        // everyTenMinutes everyThirtyMinutes hourly
    }
}
