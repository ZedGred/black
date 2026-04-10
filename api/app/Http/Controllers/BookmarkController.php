<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Http\Resources\ArticleResource;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    // POST /api/articles/{article}/bookmark
    public function bookmark(string $id)
    {
        $article = Article::findOrFail($id);
        $user = auth()->user();

        if ($user->bookmarkedArticles()->where('article_id', $id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Article already bookmarked',
            ], 409);
        }

        $user->bookmarkedArticles()->attach($id);

        return response()->json([
            'success' => true,
            'message' => 'Article bookmarked',
        ]);
    }

    // DELETE /api/articles/{article}/bookmark
    public function unbookmark(string $id)
    {
        $user = auth()->user();
        $user->bookmarkedArticles()->detach($id);

        return response()->json([
            'success' => true,
            'message' => 'Bookmark removed',
        ]);
    }

    // GET /api/bookmarks
    public function index()
    {
        $user = auth()->user();
        $articles = $user->bookmarkedArticles()
            ->with(['user', 'category'])
            ->withCount('likedUsers')
            ->withCount('comments')
            ->orderByPivot('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Bookmarks fetched',
            'data'    => ArticleResource::collection($articles)->response()->getData(true),
        ]);
    }
}
