<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProficiencyLevel extends Model
{
    protected $table = 'proficiency_levels';

    protected $fillable = [
        'cd',
        'name',
        'abbv',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
