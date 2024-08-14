<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Content;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

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
        $comment->user_id = Auth::id(); 
        $comment->content_id = $contentId;
    
        if (Gate::allows('admin')) {
            $comment->is_admin = 1;
            $content->status = 0;
        } else {
            $comment->is_admin = 0;
        }
    
        $content->save();
        $comment->save();
    
    
        return response()->json($comment->load('user'), 201);
    }

    public function index($contentId)
    {
        $comments = Comment::with('user') 
                            ->where('content_id', $contentId)
                            ->orderBy('is_admin', 'desc') 
                            ->orderBy('created_at', 'asc') 
                            ->get();
        return response()->json($comments);
    }

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
