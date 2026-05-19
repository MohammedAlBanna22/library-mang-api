<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMemberRequest extends FormRequest
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
           // 'name' => 'sometimes|required|string|max:255',// we use sometimes to allow partial updates, so only the fields that are sent in the request will be validated
            // 'email' => [
            //             'sometimes|required',
            //             'email',
            //             Rule::unique('members','email')
            //             ->ignore($this->route('member')->id),
            //             ],// to ignore the current member's email when updating
            'address' => 'nullable|string|max:255',
            'membership_date' => 'sometimes|required|date',
            'status' => 'sometimes|required',

        ];
    }
}
