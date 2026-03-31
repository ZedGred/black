<?php

namespace App\Services;

use App\Models\Article;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class ArticleService
{
    /**
     * Get paginated published articles.
     */
    public function getPublishedArticles(int $perPage = 10): LengthAwarePaginator
    {
        return Article::with(['user', 'category'])
            ->withCount('likedUsers')
            ->withCount('comments')
            ->where('status', 'published')
            ->orderBy('published_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get a single article with full detail (comments, likes, user info).
     */
    public function getArticleDetail(string $id, ?string $authUserId = null): Article
    {
        $article = Article::with(['user', 'category'])
            ->withCount('likedUsers')
            ->withCount(['likedUsers as liked_by_user_count' => function ($q) use ($authUserId) {
                if ($authUserId) {
                    $q->where('user_id', $authUserId);
                }
            }])
            ->with(['comments' => function ($q) use ($authUserId) {
                $q->with('user:id,name')
                    ->withCount('likedUsers')
                    ->withCount(['likedUsers as liked_by_user_count' => function ($qq) use ($authUserId) {
                        if ($authUserId) {
                            $qq->where('user_id', $authUserId);
                        }
                    }]);
            }])
            ->findOrFail($id);

        // Map liked_by_user boolean
        $article->liked_by_user = ($article->liked_by_user_count ?? 0) > 0;
        unset($article->liked_by_user_count);

        $article->comments->transform(function ($comment) {
            $comment->liked_by_user = ($comment->liked_by_user_count ?? 0) > 0;
            unset($comment->liked_by_user_count);
            return $comment;
        });

        return $article;
    }

    /**
     * Create a new article.
     */
    public function create(array $data, string $userId): Article
    {
        $data['user_id'] = $userId;

        if (isset($data['status']) && $data['status'] === 'published') {
            $data['published_at'] = now();
        } else {
            $data['status'] = 'draft';
        }

        return Article::create($data);
    }

    /**
     * Update an existing article.
     */
    public function update(Article $article, array $data): Article
    {
        $data['user_id'] = auth()->id();
        $article->update($data);
        $article->refresh();

        return $article;
    }

    /**
     * Delete an article.
     */
    public function delete(Article $article): void
    {
        $article->delete();
    }

    /**
     * Get published articles for a specific user.
     */
    public function getUserArticles(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return $user->articles()
            ->with('user')
            ->where('status', 'published')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get draft articles for the authenticated user.
     */
    public function getMyDrafts(string $userId, int $perPage = 10): LengthAwarePaginator
    {
        return Article::where('user_id', $userId)
            ->where('status', 'draft')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get published articles belonging to the authenticated user.
     */
    public function getMyPublishedArticles(string $userId, int $perPage = 10): LengthAwarePaginator
    {
        return Article::where('user_id', $userId)
            ->where('status', 'published')
            ->withCount('likedUsers')
            ->withCount('comments')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Publish a draft article owned by the authenticated user.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Exception
     */
    public function publishDraft(string $id, string $userId): Article
    {
        $article = Article::where('user_id', $userId)->findOrFail($id);

        if ($article->status === 'published') {
            throw new \Exception('Article already published', 400);
        }

        $article->update([
            'status'       => 'published',
            'published_at' => now(),
        ]);

        return $article;
    }
}
