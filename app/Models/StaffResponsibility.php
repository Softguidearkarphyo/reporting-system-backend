<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffResponsibility extends Model
{
    protected $table = 'staff_responsibilities';

    protected $fillable = [
        'staff_id',
        'responsibility_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
