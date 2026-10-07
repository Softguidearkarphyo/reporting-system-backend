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
    private const OFFICE_LATITUDE  = 17.8304335;
    private const OFFICE_LONGITUDE = 99.1319546;
    private const DEFAULT_OFFICE_RADIUS = 200;

    private array $allowedIps = [
        '127.0.0.1',
        '::1',
        '172.19.0.0/16',
        '172.20.0.0/16',
        '103.xxx.xxx.xxx'
    ];

    public function checkIn(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'staff_id'    => 'required|exists:staffs,id',
                'latitude'    => 'nullable|numeric',
                'longitude'   => 'nullable|numeric',
                'accuracy'    => 'nullable|numeric',
                'is_laptop'   => 'nullable|boolean',
                'device_uuid' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => 'fail',
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            $staff    = Staff::findOrFail($request->staff_id);
            $clientIp = $request->ip();
            $userLat  = (float) ($request->latitude ?? 0);
            $userLng  = (float) ($request->longitude ?? 0);

            $userAgent = $request->header('User-Agent');
            $isMobile  = (bool) preg_match('/(android|bb\d+|meego).+mobile|blackberry|iphone|ipod/i', $userAgent);
            $isTablet  = (bool) preg_match('/(ipad|tablet|(android(?!.*mobile)))/i', $userAgent);
            
            $isLaptopClient = $request->boolean('is_laptop');
            
            $deviceType = 'desktop';
            if ($isMobile) {
                $deviceType = 'mobile';
            } elseif ($isTablet) {
                $deviceType = 'tablet';
            } elseif ($isLaptopClient) {
                $deviceType = 'laptop';
            }


             return response()->json([
                    'status'  => 'success',
                    'message' => [$clientIp, $userLat, $userLng, $deviceType,$userAgent, $isLaptopClient, $staff->work_type],
                ],200);

            $isOfficeIp = $this->isAllowedIp($clientIp);

            if ($staff->work_type === 'onsite') {
                if (!$isOfficeIp) {
                    return response()->json([
                        'status'  => 'fail',
                        'message' => "Onsite ဝန်ထမ်းများသည် ရုံး IP ($clientIp) ဖြင့်သာ Check-in ပြုလုပ်ခွင့်ရှိပါသည်။",
                    ], 403);
                }
            }

            // 2. REMOTE STAFF CHECK (Laptop & Location Match & IP Match)
            if ($staff->work_type === 'remote') {
                if ($deviceType !== 'laptop') {
                    return response()->json([
                        'status'  => 'fail',
                        'message' => 'Remote ဝန်ထမ်းများသည် Laptop ဖြင့်သာ Check-in ဝင်ရောက်ခွင့်ရှိပါသည်။',
                    ], 403);
                }

                if (!$isOfficeIp) {
                    return response()->json([
                        'status'  => 'fail',
                        'message' => "ခွင့်မပြုထားသော IP Address ($clientIp) ဖြစ်နေပါသဖြင့် Check-in ဝင်၍မရပါ။",
                    ], 403);
                }

                if ($userLat == 0 && $userLng == 0) {
                    return response()->json([
                        'status'  => 'fail',
                        'message' => 'Remote ဝန်ထမ်းများအတွက် GPS Location မဖြစ်မနေ လိုအပ်ပါသည်။',
                    ], 400);
                }

                $targetLat = $staff->assigned_latitude ?? self::OFFICE_LATITUDE;
                $targetLng = $staff->assigned_longitude ?? self::OFFICE_LONGITUDE;
                $allowedRadius = $staff->allowed_radius_meters ?? self::DEFAULT_OFFICE_RADIUS;

                $distanceInMeters = $this->calculateDistance($targetLat, $targetLng, $userLat, $userLng);

                if ($distanceInMeters > $allowedRadius) {
                    $formattedDistance = round($distanceInMeters);
                    return response()->json([
                        'status'  => 'fail',
                        'message' => "သတ်မှတ်ထားသော တည်နေရာနှင့် မီတာ {$formattedDistance} ကွာဝေးနေသဖြင့် Check-in ဝင်၍မရပါ။ (ခွင့်ပြုချက်: မီတာ {$allowedRadius} အတွင်း)",
                    ], 400);
                }
            }

            $today = now()->toDateString();
            $currentTime = now()->toTimeString();

            $attendance = Attendance::create([
                'staff_id'    => $staff->id,
                'date'        => $today,
                'check_in_time' => $currentTime,
                'ip_address'  => $clientIp,
                'latitude'    => $userLat,
                'longitude'   => $userLng,
                'accuracy'    => $request->input('accuracy'),
                'is_laptop'   => $isLaptopClient,
                'device_type' => $deviceType,
                'device_uuid' => $request->input('device_uuid'),
            ]);

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
                'message' => 'Server တွင် အမှားအယွင်းတစ်ခု ဖြစ်ပေါ်နေပါသည်။',
            ], 500);
        }
    }

    private function isAllowedIp(string $ip): bool
    {
        foreach ($this->allowedIps as $allowedIp) {
            if (str_contains($allowedIp, '/')) {
                if ($this->ipInCidr($ip, $allowedIp)) {
                    return true;
                }
            } elseif ($ip === $allowedIp) {
                return true;
            }
        }
        return false;
    }

    private function ipInCidr(string $ip, string $cidr): bool
    {
        list($subnet, $mask) = explode('/', $cidr);
        $ipAddr = ip2long($ip);
        $subnetAddr = ip2long($subnet);
        $maskAddr = ~((1 << (32 - $mask)) - 1);

        return ($ipAddr & $maskAddr) == ($subnetAddr & $maskAddr);
    }

    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000;

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}