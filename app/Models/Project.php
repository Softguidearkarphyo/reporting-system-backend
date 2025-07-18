<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
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
        )->whereNull('deleted_at');
    }
}
