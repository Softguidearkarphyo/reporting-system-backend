<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TelegramService;

class TestTelegramBot extends Command
{
    protected $signature = 'telegram:test';
    protected $description = 'Test Telegram Bot Notification';

    public function handle()
    {
        $this->info('Sending test message to Telegram...');

        $sent = TelegramService::sendMorningNotification('Aung Aung', now()->format('h:i A'));

        if ($sent) {
            $this->info('✅ Telegram message sent successfully!');
        } else {
            $this->error('❌ Failed to send Telegram message. Check laravel.log for details.');
        }
    }
}