<?php

namespace App\Http\Controllers;

use App\Models\Comment;

class CommentLikeController extends Controller
{
    public function like(Comment $comment)
    {
        $user = auth()->user();
        if ($comment->likedUsers()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'message' => 'Already liked'
            ], 409);
        }

        $comment->likedUsers()->attach($user->id);

        return response()->json([
            'message' => 'Comment liked successfully'
        ], 201);
    }

    public function unlike(string $id)
    {
        $comment = Comment::findOrFail($id); // manual ambil model
        $this->authorize('unlike', $comment);
        $user = auth()->user();
        $comment->likedUsers()->detach($user->id);

        return response()->json([
            'message' => 'Comment unliked successfully'
        ], 200);
    }
}
