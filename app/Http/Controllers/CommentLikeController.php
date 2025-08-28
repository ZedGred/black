<?php

namespace App\Http\Controllers;

use App\Models\Comment;

class CommentLikeController extends Controller
{
    public function like(Comment $comment)
    {
        $user = auth()->user();

        // jika sudah like
        if ($comment->isLikedBy($user)) {
            return response()->json([
                'message' => 'Already liked'
            ], 409);
        }

        $comment->like($user);

        return response()->json([
            'message' => 'Comment liked successfully',
            'data' => [
                'liked_by_user' => true,
                'liked_users_count' => $comment->likesCount()
            ]
        ], 201);
    }

    public function unlike(Comment $comment)
    {
        $this->authorize('unlike', $comment);
        $user = auth()->user();

        $comment->unlike($user);

        return response()->json([
            'message' => 'Comment unliked successfully',
            'data' => [
                'liked_by_user' => false,
                'liked_users_count' => $comment->likesCount()
            ]
        ], 200);
    }
}
