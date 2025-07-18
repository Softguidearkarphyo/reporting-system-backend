<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
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

    public function skillSheet()
    {
        return $this->hasOne(SkillSheet::class);
    }
}
