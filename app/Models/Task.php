<?php

namespace App\Models;

use App\Models\TaskPerformance;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    public $timestamps = true;

    protected $table = 'tasks';

    protected $fillable = [
        'cd',
        'eng_name',
        'jp_name',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function taskPerformance(): HasMany
    {
        return $this->hasMany(
            TaskPerformance::class,
            'task_id',
            'id'
        )->whereNull('deleted_at');
    }
}
