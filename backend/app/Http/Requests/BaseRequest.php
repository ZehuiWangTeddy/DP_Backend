<?php

namespace App\Http\Requests;

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
        $resp = response()->json([
            'meta' => [
                'code' => 400,
                'message' => $validator->errors()->first(),
            ],
            'data' => [],
        ]);
        throw new HttpResponseException($resp);
    }
}
