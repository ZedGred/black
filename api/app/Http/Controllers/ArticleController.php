<?php

namespace App\Http\Controllers;

use App\Http\Requests\Article\StoreArticleRequest;
use App\Http\Requests\Article\UpdateArticleRequest;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Models\User;
use App\Services\ArticleService;

class ArticleController extends Controller
{
    public function __construct(protected ArticleService $articleService) {}

    // GET /api/articles
    public function index()
    {
        $articles = $this->articleService->getPublishedArticles();

        return response()->json([
            'status'  => 'success',
            'message' => 'Articles retrieved successfully',
            'data'    => ArticleResource::collection($articles)->response()->getData(true),
        ]);
    }

    // GET /api/articles/{article}
    public function show(string $id)
    {
        $article = $this->articleService->getArticleDetail($id, auth()->id());

        return response()->json([
            'status'  => 'success',
            'message' => 'Article fetched successfully',
            'data'    => new ArticleResource($article),
        ]);
    }

    // POST /api/articles
    public function store(StoreArticleRequest $request)
    {
        $article = $this->articleService->create($request->validated(), auth()->id());

        $message = $article->status === 'published'
            ? 'Article published successfully'
            : 'Draft saved successfully';

        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => new ArticleResource($article),
        ], 201);
    }

    // PUT /api/articles/{article}
    public function update(UpdateArticleRequest $request, string $id)
    {
        $article = Article::findOrFail($id);
        $this->authorize('update', $article);

        $article = $this->articleService->update($article, $request->validated());

        return response()->json([
            'status'  => 'success',
            'message' => 'Article updated successfully',
            'data'    => new ArticleResource($article),
        ]);
    }

    // DELETE /api/articles/{article}
    public function destroy(string $id)
    {
        $article = Article::findOrFail($id);
        $this->authorize('delete', $article);

        $this->articleService->delete($article);

        return response()->json([
            'status'  => 'success',
            'message' => 'Article deleted successfully',
        ]);
    }

    // GET /api/users/{user:name}/articles
    public function userArticle(User $user)
    {
        $data = $this->articleService->getUserArticles($user);

        return response()->json([
            'status' => 'success',
            'user'   => $user->only(['id', 'name', 'email']),
            'data'   => ArticleResource::collection($data)->response()->getData(true),
        ]);
    }

    // GET /api/articles/my/drafts
    public function myDrafts()
    {
        $drafts = $this->articleService->getMyDrafts(auth()->id());

        return response()->json([
            'status'  => 'success',
            'message' => 'Drafts fetched successfully',
            'data'    => ArticleResource::collection($drafts)->response()->getData(true),
        ]);
    }

    // GET /api/articles/my/articles
    public function myArticles()
    {
        $articles = $this->articleService->getMyPublishedArticles(auth()->id());

        return response()->json([
            'status'  => 'success',
            'message' => 'Your published articles fetched successfully',
            'data'    => ArticleResource::collection($articles)->response()->getData(true),
        ]);
    }

    // POST /api/articles/{article}/publish
    public function publishDraft(string $id)
    {
        try {
            $article = $this->articleService->publishDraft($id, auth()->id());
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 400);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Article published successfully',
            'data'    => new ArticleResource($article),
        ]);
    }
}
