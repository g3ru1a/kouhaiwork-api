<?php

namespace App\Http\Requests;

use Anik\Form\FormRequest;
use Illuminate\Validation\ValidationException;

class ForgotPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    protected function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    protected function rules(): array
    {
        return [
            'email' => 'required|email',
        ];
    }

    protected function validationFailed(): void
    {
        $response = response()->json([
            'error' => [
                'status' => '422',
                'message' => 'Incorrect Values.',
                'errors' => $this->validator->errors()
            ]
        ], 422);
        throw new ValidationException($this->validator, $response);
    }
}
