<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaskPerformance extends Model
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    public $timestamps = true;

    protected $table = 'task_performance';

    protected $fillable = [
        'date',
        'staff_id',
        'project_id',
        'task_id',
        'period',
        'created_at',
        'updated_at',
        'deleted_at',
    ];


    public function staff(): BelongsTo
    {
        return $this->belongsTo(
            Staff::class,
            'staff_id',
            'id'
        );
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(
            Project::class,
            'project_id',
            'id'
        );
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(
            Task::class,
            'task_id',
            'id'
        );
    }
}
