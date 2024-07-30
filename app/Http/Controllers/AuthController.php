<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        \Log::info('Register Request Data:', $request->all());
    
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:15'
        ]);
    
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
        ]);
    
        return response()->json(['user' => $user], 201);
    }
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
    
        $email = $request->input('email');
        $password = $request->input('password');
    
        $user = User::where('email', $email)->first();
    
        if (!$user) {
            return response()->json(['message' => '유효하지 않은 이메일입니다.'], 401);
        }
    
        if (!Hash::check($password, $user->password)) {
            return response()->json(['message' => '비밀번호를 확인해주세요.'], 401);
        }
    
        Auth::login($user);
        $token = $user->createToken('auth_token')->plainTextToken;
    
        return response()->json(['access_token' => $token, 'token_type' => 'Bearer']);
    }
    

    public function logout()
    {
        Auth::user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function checkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255',
        ]);

        $exists = User::where('email', $request->email)->exists();
        return response()->json(['exists' => $exists]);
    }
}
