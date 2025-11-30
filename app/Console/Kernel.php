<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('perpetualtask:run')->everyMinute();
        $schedule->command('send:notification')->everyMinute();
        $schedule->command('hour:before')->everyMinute();
        $schedule->command('half:past')->everyMinute();
        $schedule->command('quarter:end')->everyMinute();
        $schedule->command('disable:content')->everyMinute();
        $schedule->command('task:exception-fields')->dailyAt('00:00');
        $schedule->command( 'send:documentexpirereminder' )->dailyAt( '08:00' );

        $setting = \App\Models\Setting::first();
        if (!empty($setting->send_mail_at) && $setting->should_send_ticket_mail) {
            $schedule->command('tickets:send-mail')->at(date('H:i', strtotime($setting->send_mail_at)));
        }

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
