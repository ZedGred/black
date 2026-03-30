<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::with(['user'])
            ->withCount('likedUsers')
            ->withCount('comments')
            ->where('status', 'published')
            ->orderBy('published_at', 'desc')
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'message' => 'Articles get succesfully',
            'data' => $articles
        ]);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'sometimes|in:draft,published',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $data = $validator->validated();
        $data['user_id'] = auth()->id();
        
        if (isset($data['status']) && $data['status'] === 'published') {
            $data['published_at'] = now();
        } else {
            $data['status'] = 'draft';
        }
        
        $article = Article::create($data);
        
        $message = $article->status === 'published' 
            ? 'Article published successfully' 
            : 'Draft saved successfully';
            
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $article
        ], 201);
    }

    public function show(string $id)
    {
        $authUserId = auth()->id(); // bisa null jika guest

        $article = Article::with([
            'user',
            'comments.user', // eager load user di komentar
        ])
            ->withCount('likedUsers') // total like artikel
            ->withCount(['likedUsers as liked_by_user_count' => function ($q) use ($authUserId) {
                if ($authUserId) {
                    $q->where('user_id', $authUserId);
                }
            }])
            ->with(['comments' => function ($q) use ($authUserId) {
                $q->withCount('likedUsers') // total like komentar
                    ->withCount(['likedUsers as liked_by_user_count' => function ($qq) use ($authUserId) {
                        if ($authUserId) {
                            $qq->where('user_id', $authUserId);
                        }
                    }]);
            }])
            ->findOrFail($id);

        // Artikel liked boolean
        $article->liked_by_user = ($article->liked_by_user_count ?? 0) > 0;
        unset($article->liked_by_user_count);

        // Transform komentar liked boolean
        $article->comments->transform(function ($comment) {
            $comment->liked_by_user = ($comment->liked_by_user_count ?? 0) > 0;
            unset($comment->liked_by_user_count);
            return $comment;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Article fetched successfully',
            'data' => $article
        ]);
    }

    public function update(Request $request, string $id)
    {
        $article = Article::findOrFail($id);
        $this->authorize('update', $article);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $data['user_id'] = auth()->id();

        $article->update($data);
        $article->refresh(); // <-- ini penting

        return response()->json([
            'status' => 'success',
            'message' => 'Article updated successfully',
            'data' => $article
        ]);
    }

    public function destroy(string $id)
    {
        $article = Article::findOrFail($id);
        $this->authorize('delete', $article);
        $article->delete();

        return response()->json([
            'status' => 'succces',
            'message' => 'Article deleted successfully'
        ]);
    }

    public function userArticle(User $user)
    {
        $data = $user->articles()
            ->with('user')
            ->where('status', 'published')
            ->latest()
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'user' => $user->only(['id', 'name', 'email']),
            'data' => $data
        ]);
    }

    public function myDrafts(Request $request)
    {
        $drafts = Article::where('user_id', auth()->id())
            ->where('status', 'draft')
            ->latest()
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'message' => 'Drafts fetched successfully',
            'data' => $drafts
        ]);
    }

    public function myArticles(Request $request)
    {
        $articles = Article::where('user_id', auth()->id())
            ->where('status', 'published')
            ->withCount('likedUsers')
            ->withCount('comments')
            ->latest()
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'message' => 'Your published articles fetched successfully',
            'data' => $articles
        ]);
    }

    public function publishDraft(string $id)
    {
        $article = Article::where('user_id', auth()->id())->findOrFail($id);
        
        if ($article->status === 'published') {
            return response()->json([
                'status' => 'error',
                'message' => 'Article already published'
            ], 400);
        }

        $article->update([
            'status' => 'published',
            'published_at' => now()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Article published successfully',
            'data' => $article
        ]);
    }
}
