<?php

namespace App\Http\Controllers\Attendance;

use App\Models\Staff;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Services\TelegramService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    
    private const OFFICE_LATITUDE  = 16.8304335;
    private const OFFICE_LONGITUDE = 96.1319546;
    private const ALLOWED_RADIUS_METERS = 400; 

    private array $allowedIps = [
        '127.0.0.1',      // Local Testing
        '172.20.0.1',
        // '192.168.1.1',    // Local Office Wi-Fi
        '103.xxx.xxx.xxx' // office Static Public IP 
    ];

    public function checkIn(Request $request)
    {
       
        try {
            // 1. Form Input Validation
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

            $clientIp = $request->ip();
            $userLat  = (float) $request->latitude;
            $userLng  = (float) $request->longitude;

            if (!in_array($clientIp, $this->allowedIps) ) {
                return response()->json([
                    'status'  => 'fail',
                    'message' => "ရုံး Wi-Fi / IP ($clientIp) ဖြင့်သာ Check-in ပြုလုပ်ခွင့်ရှိပါသည်။",
                ], 403);
            }

            // 3. 📍 GPS Geofencing (Distance Calculation) စစ်ဆေးခြင်း
            $distanceInMeters = $this->calculateDistance(
                self::OFFICE_LATITUDE,
                self::OFFICE_LONGITUDE,
                $userLat,
                $userLng
            );

            if ($distanceInMeters > self::ALLOWED_RADIUS_METERS) {
                $formattedDistance = round($distanceInMeters);
                return response()->json([
                    'status'  => 'fail',
                    'message' => "သင်သည် ရုံးနှင့် မီတာ {$formattedDistance} ကွာဝေးနေသဖြင့် Check-in ဝင်၍မရပါ။ (ခွင့်ပြုချက်: မီတာ " . self::ALLOWED_RADIUS_METERS . " အတွင်း)",
                ], 400);
            }

            $today = now()->toDateString();
            $currentTime = now()->toTimeString();

            $alreadyCheckedIn = Attendance::where('staff_id', $request->staff_id)
                ->where('date', $today)
                ->exists();

            if ($alreadyCheckedIn) {
                return response()->json([
                    'status'  => 'fail',
                    'message' => 'ယနေ့အတွက် Attendance Check-in ပြုလုပ်ပြီး ဖြစ်ပါသည်။',
                ], 400);
            }

            $attendance = Attendance::create([
                'staff_id'      => $request->staff_id,
                'date'          => $today,
                'check_in_time' => $currentTime,
                'ip_address'    => $clientIp,
                'latitude'      => $userLat,
                'longitude'     => $userLng,
            ]);

            $staff = Staff::find($request->staff_id);
            $staffName = $staff->eng_name ?? $staff->name ?? 'Staff Member';
            $formattedTime = Carbon::parse($currentTime)->format('h:i A');

            $notified = TelegramService::sendMorningNotification($staffName, $formattedTime);

            if ($notified) {
                $attendance->update(['telegram_notified' => true]);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Attendance Check-in အောင်မြင်ပါသည်။',
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

    /**
     * Haversine Formula သုံး၍ တည်နေရာနှစ်ခုကြားရှိ မီတာအကွာအဝေးကို တွက်ချက်ပေးသည့် Function
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Earth radius in meters

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c; // Returns distance in meters
    }
}