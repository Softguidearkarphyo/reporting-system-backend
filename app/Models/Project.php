<?php

namespace App\Models;

use App\Models\StaffProject;
use App\Models\TaskPerformance;
use Laravel\Sanctum\HasApiTokens;
use App\Models\TaskPerformanceSetting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    public $timestamps = true;

    protected $table = 'projects';

    protected $fillable = [
        'cd',
        'eng_name',
        'jp_name',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function staffProjects(): HasMany
    {
        return $this->hasMany(
            StaffProject::class,
            'project_id',
            'id'
        )->whereNull('deleted_at');
    }

    public function taskPerformance(): HasMany
    {
        return $this->hasMany(
            TaskPerformance::class,
            'project_id',
            'id'
        );
    }

    public function taskPerformanceSetting(): HasMany
    {
        return $this->hasMany(
            TaskPerformanceSetting::class,
            'project_id',
            'id'
        );
    }
}
