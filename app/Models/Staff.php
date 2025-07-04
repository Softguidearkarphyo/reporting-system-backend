<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Staff extends Model
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
    //
    // Enable timestamps
    public $timestamps = true;

    // Optional: specify table name (if it's not plural or different)
    protected $table = 'staffs';

    // Fillable fields for mass-assignment
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
    ];

    // If you want password to be hidden in JSON responses
    protected $hidden = ['password'];
}
