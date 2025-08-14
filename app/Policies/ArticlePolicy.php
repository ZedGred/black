<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    public function update(User $user, Article $article): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->id === $article->user_id;
    }

    public function delete(User $user, Article $article): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->id === $article->user_id;
    }
    public function unlike(User $user, Article $article): bool
    {
        return $article->likedUsers()->where('user_id', $user->id)->exists();
    }
}
