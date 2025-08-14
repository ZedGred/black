<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    protected $fillable = ['content', 'user_id', 'article_id',];

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')
            ->with('user');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function likedUsers()
    {
        return $this->belongsToMany(User::class, 'comment_likes', 'comment_id', 'user_id')
            ->withTimestamps();
    }
}
