<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
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

    public function redirectToProvider()
    {
        $clientId = env('KAKAO_CLIENT_ID');
        $redirectUri = urlencode(env('KAKAO_REDIRECT_URI'));
        $authCodePath = 'https://kauth.kakao.com/oauth/authorize';

        $kakaoURL = $authCodePath . "?client_id={$clientId}&redirect_uri={$redirectUri}&response_type=code";
        return redirect($kakaoURL);
    }
    public function handleProviderCallback(Request $request)
    {
        $code = $request->query('code');
        if (!$code) {
            return response()->json(['message' => 'Authentication failed'], 400);
        }
    
        $clientId = env('KAKAO_CLIENT_ID');
        $clientSecret = env('KAKAO_CLIENT_SECRET');
        $redirectUri = env('KAKAO_REDIRECT_URI');
        $tokenUrl = 'https://kauth.kakao.com/oauth/token';
    
        try {
            $response = Http::post($tokenUrl, [
                'grant_type' => 'application/x-www-form-urlencoded',
                'client_id' => $clientId,
                'client_secret'=> $clientSecret,
                'redirect_uri' => $redirectUri,
                'code' => $code,
            ]);
    
            $data = $response->json();
    
            if (isset($data['access_token'])) {
                return response()->json(['access_token' => $data['access_token']]);
            } else {
                \Log::error('Kakao Token Response Error', $data);
                return response()->json(['message' => 'Authentication failed'], 400);
            }
        } catch (\Exception $e) {
            \Log::error('Kakao Token Request Exception', ['exception' => $e->getMessage()]);
            return response()->json(['message' => 'Authentication failed'], 400);
        }
    }
    
    

    
    

    private function getUserInfo($accessToken)
    {
        $response = Http::withToken($accessToken)->get('https://kapi.kakao.com/v2/user/me');
        return $response->json();
    }
}
