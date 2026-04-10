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

    protected $fillable = ['title', 'content', 'excerpt', 'image', 'user_id', 'category_id', 'status', 'published_at', 'slug'];

    protected static function booted()
    {
        static::creating(function ($article) {
            if (empty($article->slug)) {
                $base = \Illuminate\Support\Str::slug($article->title);
                $slug = $base;
                $i = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $base . '-' . $i++;
                }
                $article->slug = $slug;
            }
        });
    }

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

    public function bookmarkedByUsers()
    {
        return $this->belongsToMany(User::class, 'bookmarks', 'article_id', 'user_id')
            ->withTimestamps();
    }

    public function isBookmarkedBy(User $user): bool
    {
        return $this->bookmarkedByUsers()->where('user_id', $user->id)->exists();
    }

    public function likesCount(): int
    {
        return $this->likedUsers()->count();
    }
}
