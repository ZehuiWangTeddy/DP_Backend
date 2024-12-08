<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends BaseRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'email' => 'required|string|email:rfc,dns|max:100|unique:users', // Ensure unique email in users table
            'password' => 'required|string|min:6|max:100',
            'address' => 'nullable|string|max:255',
            'received_referral_code' => 'string|max:10|exists:users,sent_referral_code',
        ];
    }

    public function messages()
    {
        return [

        ];
    }
}
