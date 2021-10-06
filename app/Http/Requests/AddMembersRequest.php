<?php

namespace App\Http\Requests;

use Anik\Form\FormRequest;
use Illuminate\Validation\ValidationException;

class AddMembersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    protected function authorize(): bool
    {
        return auth()->guest() == false && auth()->user()->rank >= 2;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    protected function rules(): array
    {
        return [
            'users' => 'required',
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
