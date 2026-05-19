<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAuthorRequest extends FormRequest
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
        return [
            //
        'user_id' => [
                'required',
                'exists:users,id',
                'unique:authors,user_id',  // 👈 تأكد هيك مكتوبة
        ],
        'bio'=>'nullable|string',
        'nationality'=>'nullable|string',
        'phone'       => 'required|string|max:20',

        ];
    }


    public function messages(): array
    {
        return [
            'user_id.unique' => 'This user is already an author.',
            'user_id.exists' => 'This user does not exist.',
            'user_id.required' => 'User ID is required.',
        ];
    }
}
