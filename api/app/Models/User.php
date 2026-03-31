<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, HasRoles, Notifiable, HasUuids;

    protected $primaryKey =  'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = [
        'password',
        "email_verified_at",
        "remember_token"
    ];

    public function articles()
    {
        return $this->hasMany(Article::class, 'user_id');
    }
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likedArticles()
    {
        return $this->belongsToMany(Article::class, 'article_likes');
    }

    public function likedComments()
    {
        return $this->belongsToMany(Comment::class, 'comment_likes');
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'followers', 'following_id', 'follower_id')
            ->withTimestamps();
    }

    public function following()
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'following_id')
            ->withTimestamps();
    }

    public function isFollowing(User $user): bool
    {
        return $this->following()->where('following_id', $user->id)->exists();
    }

    public function followersCount(): int
    {
        return $this->followers()->count();
    }

    public function followingCount(): int
    {
        return $this->following()->count();
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class)->orderBy('created_at', 'desc');
    }

    public function unreadNotificationsCount(): int
    {
        return $this->notifications()->unread()->count();
    }


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'user_id' => $this->getKey(),
            'email' => $this->email,
            'name' => $this->name,
            'permissions' => $this->getAllPermissions()->pluck('name')
        ];
    }

    public static function validRoles(): array
    {
        return Role::pluck('name')->toArray(); // ambil semua nama role dari database
    }
}
