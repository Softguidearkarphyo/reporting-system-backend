<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Location;
use App\Models\Staff;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    private const OFFICE_LATITUDE = 16.8303998;
    private const OFFICE_LONGITUDE = 96.1319415;
    private const DEFAULT_OFFICE_RADIUS = 400;

    private array $allowedIps = [
        '127.0.0.1',
        '::1',
        '172.19.0.0/16',
        '172.20.0.0/16',
        '103.xxx.xxx.xxx',
    ];

   public function checkIn(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'staff_id'    => 'required|exists:staffs,id',
            'latitude'    => 'nullable|numeric',
            'longitude'   => 'nullable|numeric',
            'accuracy'    => 'nullable|numeric',
            'is_laptop'   => 'nullable|boolean',
            'device_uuid' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'fail',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $staff = Staff::findOrFail($request->staff_id);

        $today = now()->toDateString();
        $existingAttendance = Attendance::where('staff_id', $staff->id)
            ->where('date', $today)
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'status'  => 'fail',
                'message' => 'Already checkin for today. You cannot check in again.',
            ], 400);
        }

        $clientIp = $request->ip();
        $userLat = (float) ($request->latitude ?? 0);
        $userLng = (float) ($request->longitude ?? 0);
        $userAccuracy = (float) ($request->accuracy ?? 0);
        $requestDeviceUuid = $request->input('device_uuid');

        $userAgent = $request->header('User-Agent');
        $isMobile = (bool) preg_match('/(android|bb\d+|meego).+mobile|blackberry|iphone|ipod/i', $userAgent);
        $isTablet = (bool) preg_match('/(ipad|tablet|(android(?!.*mobile)))/i', $userAgent);
        $isLaptopClient = $request->boolean('is_laptop');

        $deviceType = 'desktop';
        if ($isMobile) {
            $deviceType = 'mobile';
        } elseif ($isTablet) {
            $deviceType = 'tablet';
        } elseif ($isLaptopClient) {
            $deviceType = 'laptop';
        }

        $isOfficeIp = $this->isAllowedIp($clientIp);

        // 1. ONSITE STAFF CHECK (work_type != 2)
        if ((int) $staff->work_type !== 2) {
            if ($deviceType !== 'desktop') {
                return response()->json([
                    'status'  => 'fail',
                    'message' => 'Onsite employees are only allowed to check in from a Desktop PC.',
                ], 403);
            }

            if (!$isOfficeIp) {
                return response()->json([
                    'status'  => 'fail',
                    'message' => "Onsite employees are only allowed to check in from the office IP ($clientIp).",
                ], 403);
            }

            $location = Location::withTrashed()->where('staff_id', $staff->id)->first();

            // Device UUID Registration & Verification
            if ($requestDeviceUuid) {
                if (!$location) {
                    try {
                        $location = Location::firstOrCreate(
                            ['staff_id' => $staff->id],
                            [
                                'lat'         => $userLat,
                                'lon'         => $userLng,
                                'allow_meter' => 400,
                                'device_uuid' => $requestDeviceUuid,
                            ]
                        );
                    } catch (\Illuminate\Database\QueryException $e) {
                        $location = Location::withTrashed()->where('staff_id', $staff->id)->first();
                    }
                }

                if (empty($location->device_uuid)) {
                    $location->update([
                        'device_uuid' => $requestDeviceUuid,
                    ]);
                } else {
                    if ($location->device_uuid !== $requestDeviceUuid) {
                        return response()->json([
                            'status'  => 'fail',
                            'message' => 'The device you are using is not registered. Please register your device first.',
                        ], 403);
                    }
                }
            }
        }

        // 2. REMOTE STAFF CHECK (work_type = 2)
        if ((int) $staff->work_type === 2) {
            if ($deviceType !== 'laptop') {
                return response()->json([
                    'status'  => 'fail',
                    'message' => 'Remote employees are only allowed to check in from a Laptop.',
                ], 403);
            }

            if ($userLat == 0 && $userLng == 0) {
                return response()->json([
                    'status'  => 'fail',
                    'message' => 'Remote employees are required to provide their GPS location.',
                ], 400);
            }

            // Retrieve staff's remote location configuration
            $location = Location::withTrashed()->where('staff_id', $staff->id)->first();

            // Device UUID Registration & Verification
            if ($requestDeviceUuid) {
                if (!$location) {
                    try {
                        $location = Location::firstOrCreate(
                            ['staff_id' => $staff->id],
                            [
                                'lat'         => $userLat,
                                'lon'         => $userLng,
                                'allow_meter' => 400,
                                'device_uuid' => $requestDeviceUuid,
                            ]
                        );
                    } catch (\Illuminate\Database\QueryException $e) {
                        $location = Location::withTrashed()->where('staff_id', $staff->id)->first();
                    }
                }

                if (empty($location->device_uuid)) {
                    $location->update([
                        'device_uuid' => $requestDeviceUuid,
                    ]);
                } else {
                    if ($location->device_uuid !== $requestDeviceUuid) {
                        return response()->json([
                            'status'  => 'fail',
                            'message' => 'The device you are using is not registered. Please register your device first.',
                        ], 403);
                    }
                }
            }

            // Radius and Distance Check
            $targetLat = $location && $location->lat ? (float) $location->lat : self::OFFICE_LATITUDE;
            $targetLng = $location && $location->lon ? (float) $location->lon : self::OFFICE_LONGITUDE;
            $allowedRadius = $location && $location->allow_meter ? (int) $location->allow_meter : self::DEFAULT_OFFICE_RADIUS;

            $distanceInMeters = $this->calculateDistance($targetLat, $targetLng, $userLat, $userLng);

            // Apply GPS Accuracy Tolerance
            if ($userAccuracy > 0) {
                $effectiveDistance = max(0, $distanceInMeters - ($userAccuracy * 0.7));
            } else {
                $effectiveDistance = $distanceInMeters;
            }

            if ($effectiveDistance > $allowedRadius) {
                $formattedDistance = round($effectiveDistance);
                return response()->json([
                    'status'  => 'fail',
                    'message' => "The location you are checking in from is {$formattedDistance} meters away from the allowed location. (Allowed: {$allowedRadius} meters)",
                ], 400);
            }
        }

        // 3. SAVE ATTENDANCE RECORD
        $currentTime = now()->toTimeString();

        $attendance = Attendance::create([
            'staff_id'          => $staff->id,
            'date'              => $today,
            'check_in_time'     => $currentTime,
            'ip_address'        => $clientIp,
            'latitude'          => $userLat,
            'longitude'         => $userLng,
            'accuracy'          => $userAccuracy,
            'is_laptop'         => $isLaptopClient,
            'device_type'       => $deviceType,
            'device_uuid'       => $requestDeviceUuid,
            'telegram_notified' => 0,
        ]);

        // Telegram Notification
        $staffName = $staff->eng_name ?? $staff->jp_name ?? 'Staff Member';
        $formattedTime = Carbon::parse($currentTime)->format('h:i A');

        $notified = TelegramService::sendMorningNotification($staffName, $formattedTime);
        if ($notified) {
            $attendance->update(['telegram_notified' => 1]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Attendance Check-in successful.',
            'data'    => $attendance,
        ], 200);

    } catch (\Exception $e) {
        Log::error('Attendance Check-in Error: ' . $e->getMessage());

        return response()->json([
            'status'  => 'error',
            'message' => 'An error occurred while processing your request.',
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
        $earthRadius = 6371000; // Earth radius in meters

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

       public function getAttendances(Request $request)
    {
        try {
            $query = Attendance::with(['staff' => function ($q) {
                $q->select('id', 'eng_name', 'jp_name', 'staff_image', 'position');
            }]);

            if ($request->filled('date')) {
                $query->whereDate('date', $request->date);
            } elseif ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('date', [$request->start_date, $request->end_date]);
            } else {
                $query->whereDate('date', Carbon::today()->toDateString());
            }

            if ($request->filled('staff_id')) {
                $query->where('staff_id', $request->staff_id);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('staff', function ($staffQuery) use ($search) {
                        $staffQuery->where('eng_name', 'like', "%{$search}%")
                                   ->orWhere('jp_name', 'like', "%{$search}%");
                    })
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('device_type', 'like', "%{$search}%");
                });
            }

            $attendances = $query->orderBy('date', 'desc')
                                 ->orderBy('check_in_time', 'desc')
                                 ->get();

            $formattedData = $attendances->map(function ($record) {
                $checkIn = Carbon::parse($record->check_in_time);
                $officialStart = Carbon::parse($record->check_in_time)->setTime(8, 30, 0);
                
                $isLate = $checkIn->greaterThan($officialStart);
                $lateMinutes = $isLate ? $officialStart->diffInMinutes($checkIn) : 0;

                return [
                    'id'            => $record->id,
                    'staff_id'      => $record->staff_id,
                    'date'          => $record->date,
                    'check_in_time' => $record->check_in_time,
                    'is_late'       => $isLate,
                    'late_minutes'  => $lateMinutes,
                    'device_type'   => $record->device_type,
                    'ip_address'    => $record->ip_address,
                    'staff'         => $record->staff,
                ];
            });

            return response()->json([
                'status'  => 200,
                'message' => 'Attendance records fetched successfully.',
                'data'    => $formattedData,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Failed to fetch attendance records.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


   
    public function deleteAttendance(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:attendances,id',
        ]);

        try {
            Attendance::where('id', $request->id)->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Attendance record deleted successfully.',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Failed to delete attendance record.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }



}