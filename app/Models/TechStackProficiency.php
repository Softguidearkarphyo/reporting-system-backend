<?php

namespace App\Models;

use App\Models\Staff;
use App\Models\TechStack;
use App\Models\ProficiencyLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TechStackProficiency extends Model
{
    protected $table = 'tech_stack_proficiencies';

    protected $fillable = [
        'staff_id',
        'tech_stack_id',
        'proficiency_level_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'id');
    }
    public function techStack(): BelongsTo
    {
        return $this->belongsTo(TechStack::class, 'tech_stack_id', 'id');
    }
    public function proficiencyLevel(): BelongsTo
    {
        return $this->belongsTo(ProficiencyLevel::class, 'proficiency_level_id', 'id');
    }
}
