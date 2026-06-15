<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'type'          => 'required|in:free,paid',
            'datetime_from' => 'required|date|after:now',
            'datetime_to'   => 'required|date|after:datetime_from',
            'organizer_id'  => 'required|integer|exists:organizers,id',
            'category_id'   => 'required|integer|exists:categories,id',
            'capacity'      => 'nullable|integer|min:1',
        ];
    }
}
