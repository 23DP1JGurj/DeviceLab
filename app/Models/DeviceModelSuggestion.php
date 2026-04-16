<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceModelSuggestion extends Model
{
    protected $fillable = [
        'device_type',
        'brand',
        'model',
        'popularity',
        'source',
    ];
}
