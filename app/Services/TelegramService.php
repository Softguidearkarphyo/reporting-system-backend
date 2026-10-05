<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    /**
     * Send Morning Attendance Check-in Notification
     *
     * @param string $staffName
     * @param string $checkInTime
     * @param string|null $customChatId
     * @return bool
     */
    public static function sendMorningNotification(string $staffName, string $checkInTime, ?string $customChatId = null): bool
    {
        $token = config('services.telegram.bot_token') ?? env('TELEGRAM_BOT_TOKEN');
        $chatId = $customChatId ?? config('services.telegram.chat_id') ?? env('TELEGRAM_CHAT_ID');

        if (!$token || !$chatId) {
            Log::error('Telegram Notification Failed: TELEGRAM_BOT_TOKEN or TELEGRAM_CHAT_ID is missing.');
            return false;
        }

        // HTML Format ဖြင့် လှပစွာ ပြင်ဆင်ထားသော စာတို
        $message = "☀️ <b>Good Morning! Attendance Check-in</b>\n\n"
                 . "👤 <b>ဝန်ထမ်းအမည်:</b> {$staffName}\n"
                 . "⏰ <b>ရောက်ရှိချိန်:</b> {$checkInTime}\n"
                 . "🏢 <b>Status:</b> Arrived at Office (ရုံးသို့ ရောက်ရှိပါပြီ)";

        try {
            $response = Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id'                  => $chatId,
                'text'                     => $message,
                'parse_mode'               => 'HTML',
                'disable_web_page_preview' => true,
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('Telegram API Response Error: ' . $response->body());
            return false;

        } catch (\Exception $e) {
            Log::error('Telegram Exception Error: ' . $e->getMessage());
            return false;
        }
    }
}