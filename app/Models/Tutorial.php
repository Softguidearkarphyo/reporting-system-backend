<?php

namespace App\Models;

use App\Models\Responsibility;
use App\Models\SkillSheet;
use App\Models\TaskPerformance;
use App\Models\TaskPerformanceSetting;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Tutorial extends Model
{
   
    use HasApiTokens, HasFactory, Notifiable;

    public $timestamps = false;

    protected $table = 'tutorials';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'password',
        'phnumber',
        'address',
        'tutorial_file',
        'position',
    ];
     
}