<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFieldUserRequest extends FormRequest
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
            'name' => 'max:255|min:1',
            'lastname' => 'max:255|min:1',
            'biography' => 'max:255|min:1',
            'age' => ['integer',],
            'address' => 'max:255|min:1',
            'password' => 'max:255|min:8',
        ];
    }
}
