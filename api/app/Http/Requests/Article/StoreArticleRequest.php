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
            'title'   => 'required|string|max:255',
            'content' => 'required|string',
            'status'  => 'sometimes|in:draft,published',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'   => 'Judul artikel wajib diisi.',
            'title.max'        => 'Judul artikel maksimal 255 karakter.',
            'content.required' => 'Konten artikel wajib diisi.',
            'status.in'        => 'Status hanya boleh draft atau published.',
        ];
    }
}
