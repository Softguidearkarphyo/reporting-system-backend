<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TechStackProficiency extends Model
{
    protected $table = 'tech_stack_proficiencies';

    protected $fillable = [
        'staff_id',
        'language_id',
        'level_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
