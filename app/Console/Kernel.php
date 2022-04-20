<?php

namespace App\Console;

use App\Console\Commands\AuctionOrders;
use App\Console\Commands\CloseExpiredOrders;
use App\Jobs\ProcessLotteryReminder;
use App\Jobs\ProcessLotteryWinner;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        AuctionOrders::class,
        CloseExpiredOrders::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('passport:purge')->daily()->withoutOverlapping();
        $schedule->command('auction:order')->everyMinute()->withoutOverlapping();
        $schedule->command('order:close')->hourly()->withoutOverlapping();
        $schedule->job(new ProcessLotteryWinner())->everyMinute()->withoutOverlapping();
        $schedule->job(new ProcessLotteryReminder(3))->dailyAt('09:00')->withoutOverlapping();
        $schedule->job(new ProcessLotteryReminder(1))->dailyAt('09:00')->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
