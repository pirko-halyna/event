<?php

namespace App\Models;

use App\Utilities\FilterBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $table = 'events';

    protected $fillable = [
        'title',
        'description',
        'type',
        'author_id',
        'category_id',
        'location_id',
        'organizer_id',
        'datetime_from',
        'datetime_to',
        'is_online',
        'capacity',
    ];

    protected $casts = [
        'is_online' => 'boolean',
        'datetime_from' => 'datetime',
        'datetime_to' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }

    public function scopeFilterBy($query, $filters)
    {
        $namespace = 'App\Utilities\EventFilters';
        $filter = new FilterBuilder($query, $filters, $namespace);

        return $filter->apply();
    }

    public function ticketTypes(): HasMany
    {
        return $this->hasMany(EventTicketType::class);
    }

    public function favouriteUsers()
    {
        return $this->belongsToMany(User::class, 'favourites', 'event_id', 'user_id');
    }
}
