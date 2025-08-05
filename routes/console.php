<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('leaves:update')->everyMinute();
