<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'unit_id',
        'stock',
        'threshold',
        'image_path',
        'is_composite',
    ];

    protected $casts = [
        'stock' => 'decimal:3',
        'threshold' => 'decimal:3',
        'is_composite' => 'boolean',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Children of this composite material.
     * trays -> (Aluminium L angle 3/4, stainless net)
     */
    public function components()
    {
        return $this->hasMany(MaterialComponent::class, 'parent_material_id');
    }

    /**
     * Parent composites that include this material as a child.
     */
    public function usedInComposites()
    {
        return $this->hasMany(MaterialComponent::class, 'child_material_id');
    }

    /**
     * Products using this material in BOM.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_materials')
            ->withPivot(['qty_per_product'])
            ->withTimestamps();
    }

    public function stockLogs()
    {
        return $this->hasMany(StockLog::class);
    }

    /**
     * Convenience helper: LOW if stock <= threshold.
     * (Used in controllers/views later.)
     */
    public function getIsLowAttribute(): bool
    {
        return (float) $this->stock <= (float) $this->threshold;
    }
}
