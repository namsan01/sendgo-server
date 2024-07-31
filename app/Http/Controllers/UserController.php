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
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'phone' => 'nullable|string|max:15',
            'current_password' => 'required_with:new_password|string',
            'new_password' => 'nullable|string|min:8',
        ]);
    
        $user = Auth::user();
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->phone = $request->input('phone');
    
        if ($request->filled('new_password')) {
            if (!Hash::check($request->input('current_password'), $user->password)) {
                return response()->json(['message' => '현재 비밀번호가 잘못되었습니다.'], 400);
            }
    
            $user->password = Hash::make($request->input('new_password'));
        }
    
        $user->save();
    
        return response()->json(['message' => '사용자 정보가 업데이트되었습니다.']);
    }
}
