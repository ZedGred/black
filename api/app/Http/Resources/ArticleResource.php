<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'title'              => $this->title,
            'content'            => $this->content,
            'excerpt'            => $this->excerpt,
            'image'              => $this->image,
            'status'             => $this->status,
            'published_at'       => $this->published_at,
            'created_at'         => $this->created_at,
            'updated_at'         => $this->updated_at,
            'liked_users_count'  => $this->liked_users_count ?? 0,
            'comments_count'     => $this->comments_count ?? 0,
            'liked_by_user'      => $this->liked_by_user ?? false,
            'author'             => $this->whenLoaded('user', fn () => new UserResource($this->user)),
            'comments'           => CommentResource::collection($this->whenLoaded('comments')),
        ];
    }
}
