<?php

namespace App\Http\Requests\Comment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => 'sometimes|required|string|max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'Isi komentar wajib diisi.',
            'content.max'      => 'Komentar maksimal 5000 karakter.',
        ];
    }
}
