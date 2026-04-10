<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $authUserId = auth()->id();

        return [
            'id'             => $this->id,
            'title'          => $this->title,
            'slug'           => $this->slug,
            'content'        => $this->content,
            'excerpt'        => $this->excerpt,
            'thumbnail'      => $this->image,
            'status'         => $this->status,
            'published_at'   => $this->published_at,
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
            'likes_count'    => $this->liked_users_count ?? 0,
            'comments_count' => $this->comments_count ?? 0,
            'is_liked'       => $this->liked_by_user ?? false,
            'is_bookmarked'  => $authUserId
                ? $this->bookmarkedByUsers()->where('user_id', $authUserId)->exists()
                : false,
            'user'           => $this->whenLoaded('user', fn () => new UserResource($this->user)),
            'categories'     => $this->whenLoaded('category', fn () => $this->category ? [new CategoryResource($this->category)] : []),
            'comments'       => CommentResource::collection($this->whenLoaded('comments')),
        ];
    }
}
