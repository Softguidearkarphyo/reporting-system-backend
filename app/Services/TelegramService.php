<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    // /**
    //  * Send Morning Attendance Check-in Notification
    //  *
    //  * @param string $staffName
    //  * @param string $checkInTime
    //  * @param string|null $customChatId
    //  * @return bool
    //  */
    public static function sendMorningNotification(string $staffName, string $checkInTime, ?string $customChatId = null): bool
    {
        $token  = env('TELEGRAM_BOT_TOKEN');
        $chatId = $customChatId ?? env('TELEGRAM_CHAT_ID');

        if (!$token || !$chatId) {
            Log::error('Telegram Notification Failed: TELEGRAM_BOT_TOKEN or TELEGRAM_CHAT_ID is missing.');
            return false;
        }

        // Parse Check-in time
        $time = Carbon::parse($checkInTime);
        $officialStartTime = $time->copy()->setTime(8, 30, 0);

        // Check if late (after 08:30 AM)
        if ($time->greaterThan($officialStartTime)) {
            $diffInMinutes = $officialStartTime->diffInMinutes($time);
            $hours   = floor($diffInMinutes / 60);
            $minutes = $diffInMinutes % 60;

            $lateString = '';
            if ($hours > 0) {
                $lateString .= "{$hours} hr ";
            }
            $lateString .= "{$minutes} mins";

            $statusText = "⚠️ Late ({$lateString})";
        } else {
            $statusText = "✅ On Time";
        }

        // Clean & Compact Telegram Message Layout
        $formattedTime = $time->format('h:i A');
        $namePadded   = str_pad($staffName, 12);
        $timePadded   = str_pad($formattedTime, 12);
        $statusPadded = str_pad($statusText, 12);

     $message = "<b>MORNING </b>\n"
     . "<code>"
     . "Name   : {$staffName}\n"
     . "Time   : {$formattedTime}\n"
     . "Status : {$statusText}\n"
     . "━━━━━━━━━━━━━━━━━━━━━━━\n"
         . "</code>";


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