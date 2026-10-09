<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\LeaveRecord;
use App\Models\Leave;
use App\Models\Staff;
use App\Models\Attendance; 

class UpdateYearlyLeaves extends Command
{
    protected $signature = 'leaves:update {target_year?}';
    protected $description = 'Carry over remaining leaves, archive previous year records, hard delete attendances, and initialize new year';

    public function handle(): int
    {
        $currentYear = (int) ($this->argument('target_year') ?? now()->year);
        $previousYear = $currentYear - 1;

        $this->info("Checking leave records for Year: {$currentYear} (Archiving: {$previousYear})...");
        Log::info("Leave rollover check started for year: {$currentYear}");

        DB::beginTransaction();
        try {
            $staffs = Staff::whereNull('deleted_at')->get();

            if ($staffs->isEmpty()) {
                $this->warn("No active staff found.");
                return self::SUCCESS;
            }

            $createdCount = 0;
            $archivedRecordIds = [];

            foreach ($staffs as $staff) {
                // check current year record exists
                $alreadyExists = LeaveRecord::withTrashed()
                    ->where('staff_id', $staff->id)
                    ->where('year', $currentYear)
                    ->exists();

                if ($alreadyExists) {
                    continue;
                }

                // find previous year record to carry over remaining leaves
                $oldRecord = LeaveRecord::where('staff_id', $staff->id)
                    ->where('year', $previousYear)
                    ->first();

                $carryOverDays = 0.0;

                if ($oldRecord) {
                    $carryOverDays = max(0, (float) $oldRecord->remain_leaves);
                    $archivedRecordIds[] = $oldRecord->id;

                    $oldRecord->delete();
                }

                $firstAnnual = 5.0;
                $secondAnnual = 5.0;
                $newTotalLeaves = 10.0 + $carryOverDays;
                $newRemainLeaves = $newTotalLeaves;

                LeaveRecord::create([
                    'staff_id'          => $staff->id,
                    'year'              => $currentYear,
                    'permanent_date'    => $staff->permanent_date ?? now()->toDateString(),
                    'carry_leaves'      => $carryOverDays,
                    'remain_leaves'     => $newRemainLeaves,
                    'first_annual'      => $firstAnnual,
                    'second_annual'     => $secondAnnual,
                    'total_used'        => 0.0,
                    'total_leaves'      => $newTotalLeaves,
                    'accumulated_hours' => 0.0,
                ]);

                $createdCount++;
            }

        
            $deletedAttendances = Attendance::withTrashed()
                ->whereYear('date', '<', $currentYear)
                ->forceDelete();

            $this->info("Hard deleted {$deletedAttendances} attendance record(s) from previous years.");
            Log::info("Hard deleted {$deletedAttendances} attendance record(s) for year transition to {$currentYear}.");
            

            // softdelete archived records
            if (!empty($archivedRecordIds)) {
                $affectedLeaves = Leave::whereIn('rec_id', $archivedRecordIds)
                    ->whereNull('deleted_at')
                    ->update(['deleted_at' => now()]);

                $this->info("Soft deleted {$affectedLeaves} leave request(s) from year {$previousYear}.");
            }

            DB::commit();

            if ($createdCount > 0) {
                $this->info("Successfully archived {$previousYear} and created {$createdCount} new records for {$currentYear}.");
                Log::info("Archived {$previousYear} and created {$createdCount} records for {$currentYear}.");
            } else {
                $this->line("All staff already have records for {$currentYear}. Skipped.");
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();

            $this->error("Failed to rollover leaves: " . $e->getMessage());
            Log::error("Leave rollover exception: " . $e->getMessage(), [
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return self::FAILURE;
        }
    }
}