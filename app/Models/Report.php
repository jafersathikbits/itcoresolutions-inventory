<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_date',
        'file_path',
        'generated_by',
    ];

    protected $casts = [
        // Always display as YYYY-MM-DD
        'report_date' => 'date:Y-m-d',
    ];

    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
