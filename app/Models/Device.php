<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Device extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'brand',
        'model',
        'serial_number',
        'warranty_until',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
