<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('leaves:update')
            ->everyMinute()
            ->timezone('Asia/Yangon');
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        // Optional console routes
        if (file_exists(base_path('routes/console.php'))) {
            require base_path('routes/console.php');
        }
    }
}
