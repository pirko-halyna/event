<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PaginationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'page'     => 'integer|min:1',
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:' . config('pagination.max_per_page')],
        ];
    }
}
