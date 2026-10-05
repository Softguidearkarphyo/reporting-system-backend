<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 
class LeaveRecord extends Model
{
    use SoftDeletes; 

    protected $table = 'leave_record';

    protected $fillable = [
        'staff_id',
        'year', 
        'permanent_date',
        'remain_leaves',
        'carry_leaves',
        'first_annual',
        'second_annual',
        'total_leaves',
        'total_used',
        'accumulated_hours',
        'created_at',
        'updated_at',
        'deleted_at',
    ];


public function leaves()
{
    return $this->hasMany(Leave::class, 'rec_id', 'id');
}

public function staff()
{
    return $this->belongsTo(Staff::class, 'staff_id', 'id');
}
}