<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OverTime extends Model
{
    protected $table = 'over_times';

    protected $fillable = [
        'staff_id',
        'ot_date',
        'ot_time',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}
