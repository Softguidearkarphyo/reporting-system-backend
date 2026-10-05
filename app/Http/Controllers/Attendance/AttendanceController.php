<?php

namespace App\Http\Controllers\Attendance;

use App\Utility;
use App\ReturnMessage;
use App\Models\Staff;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Services\TelegramService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function checkIn(Request $request)
    {
        try {
            // 1. Validation စစ်ခြင်း
            $validator = \Validator::make($request->all(), [
                'staff_id'  => 'required|exists:staffs,id',
                'latitude'  => 'required|numeric',
                'longitude' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => 'fail',
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            $today = now()->toDateString();
            $currentTime = now()->toTimeString();

            // 2. ယနေ့အတွက် Check-in ပြုလုပ်ပြီးပါက ထပ်မံပြုလုပ်ခွင့် မပေးခြင်း
            $alreadyCheckedIn = Attendance::where('staff_id', $request->staff_id)
                ->where('date', $today)
                ->exists();

            if ($alreadyCheckedIn) {
                return response()->json([
                    'status'  => 'fail',
                    'message' => 'ယနေ့အတွက် Attendance Check-in ပြုလုပ်ပြီး ဖြစ်ပါသည်။',
                ], 400);
            }

            // 3. Attendance DB Record သိမ်းဆည်းခြင်း
            $attendance = Attendance::create([
                'staff_id'      => $request->staff_id,
                'date'          => $today,
                'check_in_time' => $currentTime,
                'ip_address'    => $request->ip(),
                'latitude'      => $request->latitude,
                'longitude'     => $request->longitude,
            ]);

            // 4. ဝန်ထမ်းအမည် ရယူခြင်းနှင့် Telegram Notification ပို့ခြင်း
            $staff = Staff::find($request->staff_id);
            $staffName = $staff->name ?? 'Staff Member';
            $formattedTime = Carbon::parse($currentTime)->format('h:i A');

            $notified = TelegramService::sendMorningNotification($staffName, $formattedTime);

            if ($notified) {
                $attendance->update(['telegram_notified' => true]);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Attendance check-in အောင်မြင်ပါသည်။',
                'data'    => $attendance,
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Attendance Check-in Error: ' . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Something went wrong on the server.',
            ], 500);
        }
    }
}