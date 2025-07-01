<?php

namespace App\Http\Controllers\Auth;

use App\Models\Staff;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
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
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }
}
