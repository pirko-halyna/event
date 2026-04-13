<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class NewPasswordRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'new_password' => 'required|string|min:8|confirmed',
            'token' => 'required|string|exists:password_reset_tokens,token',
        ];
    }
}
