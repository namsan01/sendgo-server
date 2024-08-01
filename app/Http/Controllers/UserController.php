<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class UserController extends Controller
{
    public function show()
    {
        return response()->json(Auth::user());
    }

    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|file|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
    
        $user = Auth::user();
        $photo = $request->file('photo');
        $photoPath = $photo->store('profile_photos', 'public');
    
        if ($user->photo_path && Storage::exists($user->photo_path)) {
            Storage::delete($user->photo_path);
        }
    
        $user->photo_path = $photoPath;
        $user->save();
    
        return response()->json([
            'photo_path' => $photoPath,
            'message' => '사진이 성공적으로 업로드되었습니다.'
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
    
        // 현재 비밀번호 확인
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'errors' => [
                    'current_password' => ['비밀번호가 틀렸습니다.']
                ]
            ], 400);
        }
    
        // 유효성 검사 및 업데이트 로직
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:15',
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);
    
        // 비밀번호 업데이트
        if ($request->new_password) {
            $user->password = Hash::make($request->new_password);
        }
    
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->save();
    
        return response()->json(['message' => '프로필이 업데이트되었습니다.']);
    }
}
