<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
     protected $fillable = [
        'staff_id',
        'date',
        'check_in_time',
        'ip_address',
        'latitude',
        'longitude',
        'telegram_notified',
    ];

}
