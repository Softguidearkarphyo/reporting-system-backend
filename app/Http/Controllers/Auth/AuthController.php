<?php

namespace App\Http\Controllers\Auth;

use App\Utility;
use App\Models\Staff;
use App\ReturnMessage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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
            ]);
            $staff = Staff::where('username', $credentials['username'])->first();
            if (!$staff || !Hash::check($credentials['password'], $staff->password)) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }
            // Generate Sanctum token (for API use)
            $token = $staff->createToken('staff-token')->plainTextToken;
            return response()->json([
                'message' => 'Logged in',
                'token' => $token,
                'staff' => $staff,
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
            return response()->json($user);
        } catch (\Throwable  $e) {
            Utility::log("AuthController::user", $e->getMessage());
            return response()->json([], ReturnMessage::INTERNAL_SERVER_ERROR);
        }
    }
}
