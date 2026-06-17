<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventTicketTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'event_id'    => $this->event_id,
            'name'        => $this->name,
            'description' => $this->description,
            'price'       => $this->price,
            'quantity'    => $this->quantity,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }
}
