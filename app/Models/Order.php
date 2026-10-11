<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_no',
        'placed_by',
        'status',
        'placed_at',
        'notes',
    ];

    protected $casts = [
        'placed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'placed_by');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function stockLogs()
    {
        return $this->hasMany(StockLog::class);
    }
}
