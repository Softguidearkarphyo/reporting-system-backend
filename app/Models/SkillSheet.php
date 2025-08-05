<?php

namespace App\Models;

use App\Models\Staff;
use App\Models\Position;
use App\Models\TechStack;
use Laravel\Sanctum\HasApiTokens;
use App\Models\TechStackProficiency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SkillSheet extends Model
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    public $timestamps = true;

    protected $table = 'skill_sheets';

    protected $fillable = [
        'staff_id',
        'position_id',
        'grade_id',
        'join_date',
        'sg_experience',
        'prev_experience',
        'total_experience',
        'japanese_level_id',
        'major_tech_stack_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id', 'id');
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class, 'grade_id', 'id');
    }

    public function japaneseLevel(): BelongsTo
    {
        return $this->belongsTo(JapaneseLevel::class, 'japanese_level_id', 'id');
    }

    public function techStack(): BelongsTo
    {
        return $this->belongsTo(TechStack::class, 'major_tech_stack_id', 'id');
    }

    public function staffProejct(): HasMany
    {
        return $this->hasMany(StaffProject::class, 'staff_id', 'staff_id')->whereNull('deleted_at');
    }

    public function staffResponsibility(): HasMany
    {
        return $this->hasMany(StaffResponsibility::class, 'staff_id', 'staff_id')->whereNull('deleted_at');
    }

    public function techStackProficiencies(): HasMany
    {
        return $this->hasMany(TechStackProficiency::class, 'staff_id', 'staff_id');
    }
}
