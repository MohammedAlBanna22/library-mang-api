<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
    $isAdmin = auth()->user()->role === 'admin';

    return [
        'title'        => 'sometimes|required|string|max:255',
        'isbn'         => [
            'sometimes',
            'required',
            'string',
            Rule::unique('books', 'isbn')
                ->ignore($this->route('book')->id),
        ],
        'description'  => 'nullable|string',
        'author_id'    => $isAdmin
            ? 'sometimes|exists:authors,id'  // 👈 Admin بس يغيّر الـ author
            : 'prohibited',                   // 👈 Author ما يغيّر
        'genre'        => 'nullable|string',
        'published_at' => 'nullable|date',
        'total_copies' => 'sometimes|required|integer|min:1',
        'price'        => 'nullable|numeric|min:0',
        'cover_image'  => 'nullable|string',
    ];
    }

    public function messages(): array
    {
    return [
        'isbn.unique'        => 'This ISBN is already taken.',
        'author_id.exists'   => 'This author does not exist.',
        'author_id.prohibited' => 'You are not allowed to change the author.',
        'total_copies.min'   => 'Total copies must be at least 1.',
    ];
    }


}