<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Leave extends Model
{
    use SoftDeletes;

    protected $table = 'leaves';

    protected $fillable = [
        'rec_id',
        'leave_type',
        'leave_date',
        'day_count',
        'duration',
        'reason',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function leaveRecord()
    {
        return $this->belongsTo(LeaveRecord::class, 'rec_id', 'id');
    }

    public function leave_records()
    {
        return $this->leaveRecord();
    }
}