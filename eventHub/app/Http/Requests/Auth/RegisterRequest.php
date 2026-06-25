<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'phone'      => 'nullable|string|min:9',
            'email'      => 'required|email|unique:users',
            'password'   => ['required', 'string', 'max:72', 'confirmed', Password::min(8)->mixedCase()->numbers()->uncompromised()],
        ];
    }
}
