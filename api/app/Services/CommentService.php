<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Article;

class CommentService
{
    /**
     * Create a comment and return it with user + like info.
     */
    public function create(Article $article, string $userId, string $content): Comment
    {
        $comment = Comment::create([
            'content'    => $content,
            'user_id'    => $userId,
            'article_id' => $article->id,
        ]);

        return Comment::with('user:id,name')
            ->withCount('likedUsers')
            ->withExists(['likedUsers as is_liked_by_user' => function ($q) use ($userId) {
                $q->where('user_id', $userId);
            }])
            ->findOrFail($comment->id);
    }

    /**
     * Update a comment and return refreshed instance.
     */
    public function update(Comment $comment, array $data): Comment
    {
        $data['user_id'] = auth()->id();
        $comment->update($data);
        $comment->refresh();

        return $comment;
    }

    /**
     * Delete a comment.
     */
    public function delete(Comment $comment): void
    {
        $comment->delete();
    }
}
