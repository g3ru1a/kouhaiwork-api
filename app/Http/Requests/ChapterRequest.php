<?php

namespace App\Http\Requests;

use Anik\Form\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ChapterRequest extends FormRequest
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
        $req = $this;
        return [
            'manga_id' => 'required|string',
            'volume' => 'string',
            'groups' => 'required|array|min:1',
            'name' => 'string',
            'number' => ['required', 'numeric', Rule::unique('chapters', 'number')->where(function ($query) use ($req) {
                return $query->where('manga_id', $req->manga_id)->whereNull('deleted_at');
            })],
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
