<?php

namespace App\Http\Controllers;

use App\Models\Article;

class ArticleLikeController extends Controller
{
    public function like(Article $article)
    {
        $user = auth()->user();
        if ($article->likedUsers()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'message' => 'Already liked'
            ], 409);
        }

        $article->likedUsers()->attach($user->id);

        return response()->json([
            'message' => 'Article liked successfully'
        ], 201);
    }

    public function unlike(string $id)
    {
        $article = Article::findOrFail($id); // manual ambil model
        $this->authorize('unlike', $article);
        $user = auth()->user();
        $article->likedUsers()->detach($user->id);

        return response()->json([
            'message' => 'Article unliked successfully'
        ], 200);
    }
}
