<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::with(['user', 'comments'])
            ->withCount('likedUsers')
            ->orderBy('created_at', 'desc') // terbaru di atas
            ->paginate(10);

        return response()->json($articles);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $data['user_id'] = auth()->id();

        $article = Article::create($data);

        return response()->json([
            'message' => 'Article created successfully',
            'data' => $article
        ], 201);
    }

    public function show(string $id)
    {
        $article = Article::with(['user', 'comments'])
            ->withCount('likedUsers') // ini hitung jumlah user yang nge-like
            ->findOrFail($id);

        return response()->json($article);
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
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $data['user_id'] = auth()->id();

        $article->update($data);
        $article->refresh(); // <-- ini penting

        return response()->json([
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
            'message' => 'Article deleted successfully'
        ]);
    }

    public function userArticle(User $user)
    {
        $stories = $user->articles()
            ->with('user') // eager load penulis
            ->latest()
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'user' => $user->only(['id', 'name', 'email']),
            'stories' => $stories
        ]);
    }
}
