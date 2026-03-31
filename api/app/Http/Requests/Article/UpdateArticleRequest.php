<?php

namespace App\Http\Requests\Article;

use Illuminate\Foundation\Http\FormRequest;

class UpdateArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'   => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
        ];
    }

    public function messages(): array
    {
        return [
            'title.max' => 'Judul artikel maksimal 255 karakter.',
        ];
    }
}
