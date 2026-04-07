<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;
    use HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['title', 'content', 'user_id', 'category_id', 'status', 'published_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likedUsers()
    {
        return $this->belongsToMany(User::class, 'article_likes', 'article_id', 'user_id')
            ->withTimestamps();
    }

    public function isLikedBy(User $user): bool
    {
        return $this->likedUsers()->where('user_id', $user->id)->exists();
    }

    public function like(User $user)
    {
        $this->likedUsers()->attach($user->id);;
    }

    public function unlike(User $user)
    {
        $this->likedUsers()->detach($user->id);
    }

    public function likesCount(): int
    {
        return $this->likedUsers()->count();
    }
}
