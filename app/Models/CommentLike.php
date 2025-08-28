<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CommentLike extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'comment_likes'; // nama table pivot
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['comment_id', 'user_id'];

    // Relasi ke comment
    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }

    // Relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
