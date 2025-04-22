<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Log;
use Hash;
class AuthController extends Controller
{
    //
    public function login(Request $request){
        $request->validate([
            'phone' => 'required|string|exists:users,phone',
            'password' => 'required|string|min:6',
        ]);

        $user = User::where('phone', $request->phone)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            Log::info('Invalid login credentials, '.$request->phone);
            return response()->json([
                'message' => 'Invalid login credentials.',
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        Log::info('User logged in, '.$user->phone);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 200);
    }
    public function logout(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();
        Log::info('User logged out, '.$user->phone);
        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }
    public function changePassword(Request $request){
        $request->validate([
            'old_password' => 'required|string|min:6',
            'new_password' => 'required|string|min:6',
        ]);
        $user = auth()->user();

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Old password is incorrect'
            ], 400);
        }
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Password changed successfully'
        ], 200);
        
    }
}
