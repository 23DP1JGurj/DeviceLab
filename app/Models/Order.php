<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number','user_id','branch_id','device_id','status',
        'problem_description','diagnosis','work_log',
        'estimated_cost','final_cost','started_at','finished_at',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
