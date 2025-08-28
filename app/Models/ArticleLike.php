<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ArticleLike extends Model
{
    use HasUuids;

    protected $table = 'article_likes';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['article_id', 'user_id'];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
