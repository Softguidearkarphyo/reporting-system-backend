<?php

namespace App\Models;

use App\Models\OverTime;
use App\Models\SkillSheet;
use App\Models\TaskPerformance;
use Laravel\Sanctum\HasApiTokens;
use App\Models\StaffResponsibility;
use App\Models\TaskPerformanceSetting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Staff extends Model
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    public $timestamps = true;

    protected $table = 'staffs';

    protected $fillable = [
        'staff_no',
        'eng_name',
        'jp_name',
        'username',
        'password',
        'address',
        'ph_number',
        'position',
        'role',
        'email',
        'permanent_date',
        'ref_person',
        'ref_ph_number',
        'project',
        'sort_key',
        'staff_image',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $hidden = ['password'];

    public function staffProjects(): HasMany
    {
        return $this->hasMany(
            StaffProject::class,
            'staff_id',
            'id'
        )->whereNull('deleted_at');
    }

    public function skillSheet(): HasOne
    {
        return $this->hasOne(SkillSheet::class, 'staff_id', 'id');
    }
    public function staffResponsibility(): HasMany
    {
        return $this->hasMany(StaffResponsibility::class, 'staff_id', 'id')->whereNull('deleted_at');
    }
    public function taskPerformance(): HasMany
    {
        return $this->hasMany(
            TaskPerformance::class,
            'staff_id',
            'id'
        );
    }
    public function taskPerformanceSetting(): HasMany
    {
        return $this->hasMany(
            TaskPerformanceSetting::class,
            'staff_id',
            'id'
        );
    }
    public function leave_records()
    {
        return $this->hasMany(LeaveRecord::class, 'staff_id');
    }
    public function over_times()
    {
        return $this->hasMany(OverTime::class, 'staff_id');
    }
    public function staffFine(): HasMany
    {
        return $this->hasMany(StaffFine::class, 'staff_id', 'id');
    }
    public function location(): HasOne
    {
        return $this->hasOne(Location::class, 'staff_id', 'id');
    }
}
