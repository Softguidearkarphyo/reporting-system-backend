<?php

namespace App\Http\Controllers\Auth;

use App\Utility;
use App\Models\Staff;
use App\ReturnMessage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Staff\StaffResource;
use App\Models\Location;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
                'lat'      => 'nullable|numeric',
                'lon'      => 'nullable|numeric',
            ]);
            $staff = Staff::where('username', $credentials['username'])->first();

            if (!$staff) {
                return response()->json([
                    'message' => "Username does not match!",
                ], 422);
            }
            if (!Hash::check($credentials['password'], $staff->password)) {
                return response()->json([
                    'message' => 'Password is incorrect',
                ], 401);
            }
            if (isset($credentials['lat']) && isset($credentials['lon'])) {
                $this->saveLocation($staff->id, $credentials['lat'], $credentials['lon']);
            }
            $token = $staff->createToken('staff-token')->plainTextToken;
            return response()->json([
                'message' => 'Logged in',
                'token' => $token,
                'staff' => new StaffResource($staff),
            ]);
        } catch (\Throwable  $e) {
            Utility::log("AuthController::login", $e->getMessage());
            return response()->json([], ReturnMessage::INTERNAL_SERVER_ERROR);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'Logged out']);
        } catch (\Throwable  $e) {
            Utility::log("AuthController::logout", $e->getMessage());
            return response()->json([], ReturnMessage::INTERNAL_SERVER_ERROR);
        }
    }

    public function user()
    {
        try {
            $user = auth('sanctum')->user();
            if ($user) {
                $user = new StaffResource($user);
            }
            return response()->json($user);
        } catch (\Throwable  $e) {
            Utility::log("AuthController::user", $e->getMessage());
            return response()->json([], ReturnMessage::INTERNAL_SERVER_ERROR);
        }
    }

    public function saveLocation($id, $lat, $lng)
    {
        try {
            Location::upsert(
                [
                    [
                        'staff_id' => $id,
                        'lat' => $lat,
                        'lng' => $lng,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                ],
                ['staff_id'],
                ['lat', 'lng', 'updated_at']
            );
        } catch (\Throwable  $e) {
            Utility::log("AuthController::saveLocation", $e->getMessage());
        }
    }
}
