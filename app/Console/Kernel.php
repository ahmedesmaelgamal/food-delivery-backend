<?php

namespace App\Console;

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
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
//    protected function schedule(Schedule $schedule)
//    {
//        // $schedule->command('inspire')
//        //          ->hourly();
//        // $schedule->command('sync:wordpress-products')
//        //     ->everyMinute();
//
//    }






    protected function schedule(Schedule $schedule)
    {
        $schedule->command('queue:work --sleep=3 --tries=3 --timeout=90')->everyMinute();

        // ═══════════════════════════════════════════════════════════
        // HIGH FREQUENCY - Every 5 minutes
        // ═══════════════════════════════════════════════════════════

        // Sync variations 5 minutes
        $schedule->command('wordpress:sync variations')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->runInBackground();


        // Sync English categories every 5 minutes
        $schedule->command('wordpress:sync categories-en')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->runInBackground();

        // Sync Arabic categories every 5 minutes
        $schedule->command('wordpress:sync categories-ar')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->runInBackground();

        // Sync shipping zones every 5 minutes
        $schedule->command('wordpress:sync shipping-zones')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->runInBackground();

        // ═══════════════════════════════════════════════════════════
        // MEDIUM FREQUENCY - Every 15 minutes
        // ═══════════════════════════════════════════════════════════

        // Sync cities/locations every 15 minutes
        $schedule->command('wordpress:sync cities')
            ->everyFifteenMinutes()
            ->withoutOverlapping()
            ->runInBackground();

        // Sync shipping zone locations every 15 minutes
        $schedule->command('wordpress:sync shipping-zone-locations')
            ->everyFifteenMinutes()
            ->withoutOverlapping()
            ->runInBackground();

        // Sync shipping zone methods every 15 minutes
        $schedule->command('wordpress:sync shipping-zone-methods')
            ->everyFifteenMinutes()
            ->withoutOverlapping()
            ->runInBackground();

        // ═══════════════════════════════════════════════════════════
        // LOW FREQUENCY - Every 30 minutes
        // ═══════════════════════════════════════════════════════════

        // Sync Buy It Together every 30 minutes
        $schedule->command('wordpress:sync buy-it-together')
            ->everyThirtyMinutes()
            ->withoutOverlapping()
            ->runInBackground();

        // Sync product reviews every 30 minutes
        $schedule->command('wordpress:sync reviews')
            ->everyThirtyMinutes()
            ->withoutOverlapping()
            ->runInBackground();


        $schedule->command('wordpress:sync banners')
            ->everyThirtyMinutes()
            ->withoutOverlapping()
            ->runInBackground();
        // ═══════════════════════════════════════════════════════════
        // HOURLY - Once per hour
        // ═══════════════════════════════════════════════════════════

        // Sync partners hourly
        $schedule->command('wordpress:sync partners')
            ->hourly()
            ->withoutOverlapping()
            ->runInBackground();

        // ═══════════════════════════════════════════════════════════
        // NOTES:
        // ═══════════════════════════════════════════════════════════
        // - withoutOverlapping() prevents the same job from running twice
        // - runInBackground() allows multiple jobs to run simultaneously
        // - Adjust frequencies based on your data change patterns
        // - More frequent = higher server load but fresher data
        //
        // Available schedule methods:
        // ->everyMinute()           // Every minute
        // ->everyTwoMinutes()       // Every 2 minutes
        // ->everyFiveMinutes()      // Every 5 minutes
        // ->everyTenMinutes()       // Every 10 minutes
        // ->everyFifteenMinutes()   // Every 15 minutes
        // ->everyThirtyMinutes()    // Every 30 minutes
        // ->hourly()                // Every hour
        // ->hourlyAt(17)            // Every hour at 17 minutes past
        // ->everyTwoHours()         // Every 2 hours
        // ->daily()                 // Daily at midnight
        // ->dailyAt('13:00')        // Daily at 1 PM
        // ->weekly()                // Weekly on Sunday
        // ->monthly()               // Monthly on 1st
        // ->cron('* * * * *')       // Custom cron expression
        // ═══════════════════════════════════════════════════════════
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
