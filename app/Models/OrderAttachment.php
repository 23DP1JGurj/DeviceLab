<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderAttachment extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
    ];

    protected $appends = [
        'url',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getUrlAttribute(): string
    {
        $path = str_replace('\\', '/', ltrim($this->file_path, '/'));

        return "/storage/{$path}";
    }
}
