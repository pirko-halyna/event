<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'         => 'sometimes|required|string|max:255',
            'description'   => 'nullable|string',
            'type'          => 'sometimes|required|in:free,paid',
            'datetime_from' => 'sometimes|required|date',
            'datetime_to'   => 'sometimes|required|date|after:datetime_from',
            'organizer_id'  => 'sometimes|required|integer|exists:organizers,id',
            'category_id'   => 'sometimes|required|integer|exists:categories,id',
            'capacity'      => 'nullable|integer|min:1',
        ];
    }
}
