<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\LeaveRecord;
use App\Utility;

class UpdateYearlyLeaves extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leaves:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        Log::info('Scheduler ping worked: ' . now());
        $yearEnd = Carbon::create(now()->year, 12, 31)->endOfDay();

        LeaveRecord::whereDate('permanent_date', '<=', $yearEnd)
            ->whereHas('staff', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->get()
            ->each(function ($leave) {
                $leave->carry_leaves += 10;
            });

        $this->info('Leave updated!');
        Utility::log('UpdateYearlyLeaves command running at ' . now(), 'info');
        return self::SUCCESS;
    }
}
