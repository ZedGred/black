<?php

namespace App\Http\Requests\Article;

use Illuminate\Foundation\Http\FormRequest;

class StoreArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => 'required|string|max:255',
            'content'     => 'required|string',
            'excerpt'     => 'nullable|string|max:500',
            'thumbnail'   => 'nullable|string',
            'category_id' => 'nullable|uuid|exists:categories,id',
            'status'      => 'sometimes|in:draft,published',
        ];
    }
}
