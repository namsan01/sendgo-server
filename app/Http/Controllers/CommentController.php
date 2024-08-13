<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment; // 댓글 모델
use App\Models\Content; // 문의 모델
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request, $contentId)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);
    
        $content = Content::findOrFail($contentId);
    
        $comment = new Comment();
        $comment->content = $request->input('content');
        $comment->user_id = Auth::id(); // 현재 로그인한 사용자 ID
        $comment->content_id = $contentId; // 관련 문의 ID
        $comment->save();
    
        return response()->json($comment, 201);
    }
    

    // 특정 문의의 모든 댓글 조회
    public function index($contentId)
    {
        $comments = Comment::where('content_id', $contentId)->get();
        return response()->json($comments);
    }

    // 댓글 삭제
    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);

        if (Auth::id() !== $comment->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $comment->delete();
        return response()->json(['message' => 'Comment deleted successfully']);
    }
}