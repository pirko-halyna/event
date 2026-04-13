<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

class IndexEventRequest extends PaginationRequest
{
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'locations' => 'nullable|array',
            'locations.*' => 'integer',
            'is_online' => 'boolean',
            'datetime_from' => 'date',
            'datetime_to' => 'date',
            'organizer_id' => 'integer',
            'categories' => 'array',
            'categories.*' => 'integer',
            'search' => 'nullable|string',
        ];
    }
}
