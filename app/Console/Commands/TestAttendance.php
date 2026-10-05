<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Staff;
use App\Services\TelegramService;

class TestAttendance extends Command
{
    protected $signature = 'test:attendance';
    protected $description = 'Test Telegram Attendance System';

    public function handle()
    {
        // Required Fields များကို ဖြည့်သွင်း၍ Staff ထည့်ခြင်း
        $staff = Staff::firstOrCreate(
            ['email' => 'aung@gmail.com'],
            [
                'staff_no'  => 'STF-001',
                'eng_name'  => 'Aung Aung',
                'jp_name'   => 'アウン アウン',
                'username'  => 'aungaung',
                'password'  => bcrypt('password123'),
                'address'   => 'Yangon',
                'position'  => 1,
                'role'      => 1,
            ]
        );

        $staffName = $staff->eng_name;
        $this->info("Testing notification for staff: {$staffName}");

        $sent = TelegramService::sendMorningNotification($staffName, now()->format('h:i A'));

        if ($sent) {
            $this->info('✅ Telegram Notification Sent Successfully!');
        } else {
            $this->error('❌ Failed! Check laravel.log file.');
        }
    }
}