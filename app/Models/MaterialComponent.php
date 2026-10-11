<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_material_id',
        'child_material_id',
        'qty_per_parent',
    ];

    protected $casts = [
        'qty_per_parent' => 'decimal:3',
    ];

    public function parentMaterial()
    {
        return $this->belongsTo(Material::class, 'parent_material_id');
    }

    public function childMaterial()
    {
        return $this->belongsTo(Material::class, 'child_material_id');
    }
}
