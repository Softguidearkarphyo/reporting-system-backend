<?php

namespace App\Models;

use App\Models\Staff;

use Illuminate\Database\Eloquent\Model;

class SkillSheet extends Model
{
    protected $table = 'skill_sheets';

    protected $fillable = [
        'staff_id',
        'grade',
        'join_date',
        'japanese_level',
        'sg_experience',
        'prev_experience',
        'total_experience',
        'responsibility',
        'expertise',
        'language_level_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}
