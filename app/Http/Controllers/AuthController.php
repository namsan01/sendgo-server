<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
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
        $user = Auth::user();

        $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:15',
            'current_password' => 'nullable|string',
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);


        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        if ($request->has('phone')) {
            $user->phone = str_replace('-', '', $request->phone);
        }    
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
        $redirectUri = (env('KAKAO_REDIRECT_URI'));
        $authCodePath = 'https://kauth.kakao.com/oauth/authorize';

        $kakaoURL = $authCodePath . "?client_id={$clientId}&redirect_uri={$redirectUri}&response_type=code";
        return redirect($kakaoURL);
    }

    public function handleProviderCallback(Request $request)
    {
        $code = $request->input('code');
        if (!$code) {
            return response()->json(['message' => 'Authorization code not provided'], 400);
        }
    
        $clientId = env('KAKAO_CLIENT_ID');
        $redirectUri = env('KAKAO_REDIRECT_URI');
        $tokenUrl = 'https://kauth.kakao.com/oauth/token';
    
        try {
            $response = Http::asForm()->post($tokenUrl, [
                'grant_type' => 'authorization_code',
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'code' => $code,
            ]);
    
            $data = $response->json();
    
            if (isset($data['access_token'])) {
                $userInfo = $this->getUserInfo($data['access_token']);
    
                if ($userInfo && isset($userInfo['id'])) {

                    $existingUser = User::where('email', $userInfo['kakao_account']['email'])->first();
    
                    if ($existingUser) {
                        $existingUser->name = $userInfo['properties']['nickname'] ?? 'Unknown';
                        $existingUser->kakao_id = $userInfo['id']; 
                        $existingUser->save();
    
                        Auth::login($existingUser);
                        $token = $existingUser->createToken('auth_token')->plainTextToken;
    
                        return response()->json([
                            'access_token' => $token,
                            'token_type' => 'Bearer'
                        ]);
                    } else {
                        $password = bcrypt('random_password_' . $userInfo['id']); 
    
                        $user = User::create([
                            'kakao_id' => $userInfo['id'],
                            'name' => $userInfo['properties']['nickname'] ?? 'Unknown',
                            'email' => $userInfo['kakao_account']['email'] ?? null,
                            'password' => $password,
                        ]);
    
                        Auth::login($user);
                        $token = $user->createToken('auth_token')->plainTextToken;
    
                        return response()->json([
                            'access_token' => $token,
                            'token_type' => 'Bearer',
                        ]);
                    }
                } else {
                    \Log::error('Kakao User Info Response Error', $userInfo);
                    return response()->json(['message' => 'User info fetch failed'], 400);
                }
            } else {
                \Log::error('Kakao Token Response Error', $data);
                return response()->json(['message' => 'Authentication failed'], 400);
            }
        } catch (\Exception $e) {
            \Log::error('Kakao Token Request Exception', ['exception' => $e->getMessage()]);
            return response()->json(['message' => 'Authentication failed'], 500);
        }
    }
    
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        $code = $request->input('code');
        if (!$code) {
            return response()->json(['message' => 'Authorization code not provided'], 400);
        }
    
        $clientId = env('GOOGLE_CLIENT_ID');
        $clientSecret = env('GOOGLE_CLIENT_SECRET');
        $redirectUri = env('GOOGLE_REDIRECT');
        $tokenUrl = 'https://oauth2.googleapis.com/token';
    
        try {
            $response = Http::asForm()->post($tokenUrl, [
                'code' => $code,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code',
            ]);
    
            $data = $response->json();
    
            // Log the response data for debugging
            \Log::info('Google Token Response:', $data);
    
            if (isset($data['access_token'])) {
                $accessToken = $data['access_token'];
    

                $userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';
                $userResponse = Http::withToken($accessToken)->get($userInfoUrl);
                $userData = $userResponse->json();
    

                $existingUser = User::where('email', $userData['email'])->first();
    
                if ($existingUser) {

                    $existingUser->name = $userData['name'];
                    $existingUser->google_id = $userData['id'];
                    $existingUser->save();
    

                    Auth::login($existingUser);
                    $token = $existingUser->createToken('auth_token')->plainTextToken;
    
                    return response()->json([
                        'access_token' => $token,
                        'token_type' => 'Bearer',
                    ]);
                } else {

                    $newUser = User::create([
                        'google_id' => $userData['id'],
                        'name' => $userData['name'],
                        'email' => $userData['email'],
                        'password' => bcrypt('temporary_password_' . $userData['id']),
                    ]);
    

                    Auth::login($newUser);
                    $token = $newUser->createToken('auth_token')->plainTextToken;
    
                    return response()->json([
                        'access_token' => $token,
                        'token_type' => 'Bearer',
                    ]);
                }
            } else {
                return response()->json(['message' => 'Failed to retrieve access token', 'error' => $data], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
    

    private function getUserInfo($accessToken)
    {
        try {
            $response = Http::withToken($accessToken)->get('https://kapi.kakao.com/v2/user/me');
            return $response->json();
        } catch (\Exception $e) {
            \Log::error('Kakao User Info Request Exception', ['exception' => $e->getMessage()]);
            return null;
        }
    }
}
