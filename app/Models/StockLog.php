<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_id',
        'user_id',
        'type',
        'qty',
        'before_stock',
        'after_stock',
        'order_id',
        'reason',
    ];

    protected $casts = [
        'qty' => 'decimal:3',
        'before_stock' => 'decimal:3',
        'after_stock' => 'decimal:3',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
