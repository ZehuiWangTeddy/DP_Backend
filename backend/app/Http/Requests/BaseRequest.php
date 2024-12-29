<?php

namespace App\Http\Requests;

use App\Helpers\StanderOutputHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class BaseRequest extends FormRequest
{
    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            StanderOutputHelper::StanderResponse(400, $validator->errors()->first())
        );
    }
}
