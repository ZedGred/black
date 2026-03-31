<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'content'           => $this->content,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
            'liked_users_count' => $this->liked_users_count ?? 0,
            'liked_by_user'     => $this->liked_by_user ?? false,
            'is_liked_by_user'  => $this->is_liked_by_user ?? false,
            'author'            => $this->whenLoaded('user', fn () => new UserResource($this->user)),
        ];
    }
}
