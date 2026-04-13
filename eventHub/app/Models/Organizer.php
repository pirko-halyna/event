<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Casts\Attribute, Factories\HasFactory, Model, Relations\HasMany};
use Illuminate\Support\Facades\Storage;

class Organizer extends Model
{
    use HasFactory;

    protected $table = 'organizers';

    protected $fillable = [
        'title',
        'description',
        'image',
    ];

    /**
     * Get image attribute with full url
     *
     * @return Attribute
     */
    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => Storage::url($value),
        );
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
