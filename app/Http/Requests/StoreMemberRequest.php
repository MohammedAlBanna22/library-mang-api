<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMemberRequest extends FormRequest
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
            //
            // 'phone' => 'required|string|max:20',
            // 👈 required للـ admin، ممنوع للـ member
            'user_id'         => $isAdmin ? 'required|exists:users,id' : 'prohibited',
            'address' => 'nullable|string|max:255',
            'membership_date' => 'required|date',
            'status' => 'required',
            'phone' => ['required', 'string', 'max:20'],
           // 'user_id' => 'sometimes|exists:users,id',

        ];
    }
}
