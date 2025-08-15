<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{

    protected $table = 'leaves';

    protected $fillable = [
        'staff_id',
        'leave_type',
        'leave_date',
        'day_count',
        'duration',
        'reason',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}
