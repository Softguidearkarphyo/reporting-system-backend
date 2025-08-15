<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRecord extends Model
{
    protected $table = 'leave_record';

    protected $fillable = [
        'staff_id',
        'permanent_date',
        'remain_leaves',
        'carry_leaves',
        'first_annual',
        'second_annual',
        'total_leaves',
        'total_used',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}
