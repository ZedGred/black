<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    public function update(User $user, Comment $comment): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->id === $comment->user_id;
    }

    public function delete(User $user, Comment $comment): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->id === $comment->user_id;
    }

    public function unlike(User $user, Comment $comment): bool
    {
        return $comment->likedUsers()->where('user_id', $user->id)->exists();
    }
}