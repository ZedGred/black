<?php

namespace App\Http\Controllers;

use App\Models\Article;

class ArticleLikeController extends Controller
{

    public function like(Article $article)
    {
        $user = auth()->user();
        if ($article->isLikedBy($user)) {
            return response()->json([
                'message' => 'Already liked'
            ], 409);
        }

        $article->like($user);
        return response()->json([
            'message' => 'Comment liked successfully',
            'data' => [
                'liked_by_user' => true,
                'liked_users_count' => $article->likesCount()
            ]
        ], 201);
    }

    public function unlike(Article $article)
    {
        $user = auth()->user();
        $article->unlike($user);

        return response()->json([
            'message' => 'Article unliked successfully',
            'data' => [
                'liked_by_user' => false,
                'liked_users_count' => $article->likesCount()
            ]
        ], 200);
    }
}
