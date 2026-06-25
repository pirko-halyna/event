<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class NewPasswordRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim($this->email ?? '')),
        ]);
    }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'max:255', 'email:rfc'],
            'code'     => ['required', 'string', 'digits:6'],
            'password' => ['required', 'string', 'max:72', 'confirmed', Password::min(8)->mixedCase()->numbers()->uncompromised()],
        ];
    }
}
