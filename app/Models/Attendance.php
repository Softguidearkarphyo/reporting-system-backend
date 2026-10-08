<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use SoftDeletes;

    protected $table = 'attendances';
    protected $fillable = [
        'staff_id',
        'date',
        'check_in_time',
        'ip_address',
        'latitude',
        'longitude',
        'telegram_notified',
        'accuracy',
        'is_laptop',
        'device_type',
        'device_uuid',
    ];

    public function staff()
{
    return $this->belongsTo(Staff::class, 'staff_id');
}

}
