<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        \Log::info('Register Request Data:', $request->all());

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:15',
            'password' => 'required|string|min:8',
        ]);

        $phone = $request->phone ? str_replace('-', '', $request->phone) : null;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $phone,
            'password' => Hash::make($request->password),
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

    public function updateProfile(Request $request)
    {
        $user = Auth::user(); // 현재 로그인된 사용자

        $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:15',
            'current_password' => 'nullable|string',
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);

        // 이름과 이메일 업데이트
        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        if ($request->has('phone')) {
            $user->phone = str_replace('-', '', $request->phone);
        }

        // 비밀번호 업데이트
        if ($request->has('current_password') && $request->has('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json(['message' => '현재 비밀번호가 일치하지 않습니다.'], 400);
            }
            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        return response()->json(['user' => $user], 200);
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        $user = Socialite::driver('google')->user();

        // 구글 사용자 정보를 바탕으로 유저를 찾거나 새로 생성
        $existingUser = User::where('email', $user->getEmail())->first();

        if ($existingUser) {
            // 이메일이 중복된 경우, 사용자 정보 업데이트
            $existingUser->name = $user->getName();
            $existingUser->google_id = $user->getId(); // 구글 ID를 업데이트
            $existingUser->save();

            // 로그인 처리
            Auth::login($existingUser);
            $token = $existingUser->createToken('auth_token')->plainTextToken;

            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer'
            ]);
        } else {
            // 새로운 사용자 등록
            $password = bcrypt('random_password_' . $user->getId()); // 임시 비밀번호

            $newUser = User::create([
                'google_id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'password' => $password,
            ]);

            // 로그인 처리
            Auth::login($newUser);
            $token = $newUser->createToken('auth_token')->plainTextToken;

            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]);
        }
    }
}
