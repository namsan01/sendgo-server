<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Content;
use Illuminate\Support\Facades\Auth;

class ContentController extends Controller
{
    /**
     * 문의 내용을 저장합니다.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // 요청 유효성 검사
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|integer|in:1,2' // 상태는 1 또는 2만 허용
        ]);

        // 인증된 사용자 가져오기
        $user = Auth::user();

        // 사용자 인증이 되어 있지 않은 경우
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // 콘텐츠 저장
        $content = Content::create([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'status' => $request->input('status'),
            'user_id' => $user->id // 사용자 ID 설정
        ]);

        return response()->json(['message' => 'Content saved successfully', 'data' => $content], 201);
    }

    /**
     * 모든 문의 내역을 가져옵니다.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // 모든 문의 내역을 가져오고 작성자 정보도 함께 조회
        $contents = Content::with('user')->latest()->get(); // 최신 콘텐츠부터 가져옴
        return response()->json($contents);
    }

    /**
     * 특정 문의 내역을 가져옵니다.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // 특정 문의 내역을 가져오고 작성자 정보도 함께 조회
        $content = Content::with('user')->find($id);

        if (!$content) {
            return response()->json(['message' => 'Content not found'], 404);
        }

        return response()->json($content);
    }
}
