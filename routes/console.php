<?php

use Illuminate\Support\Facades\Schedule;

// Schedule::command('leaves:update')->everyMinute();
Schedule::command('leaves:update')
    ->cron('1 0 1 1 *')
    ->timezone('Asia/Yangon');
