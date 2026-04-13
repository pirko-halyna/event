<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PasswordResetToken extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $primaryKey = 'email';
    public $incrementing = false;

    public function __construct(array $attributes = [])
    {
        $attributes['token'] = Str::random(56);
        parent::__construct($attributes);
    }

    protected $fillable = [
        'email',
        'token',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }
}
