<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffFine extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'staff_id',
        'date',
        'time',
        'amount',
        'count',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}
