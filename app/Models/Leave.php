<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{

    protected $table = 'leaves';

    protected $fillable = [
        'staff_id',
        'leave_type',
        'start_date',
        'end_date',
        'leave_date',
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
