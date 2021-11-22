<?php

namespace App\Console;

use App\Console\Commands\Cronjob\SendEmail;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\Cronjob\UpdateProductPassedDays;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        UpdateProductPassedDays::class,
        SendEmail::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('cron:send:remind_email')->everyThirtyMinutes()->appendOutputTo(storage_path('logs/cron_send_remind_email.log'));
        $schedule->command('cron:update:product_passed_days')->dailyAt('00:00')->appendOutputTo(storage_path('logs/cron_update_product_passed_days.log'));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
