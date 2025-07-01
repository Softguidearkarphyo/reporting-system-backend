<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Staff extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    //
    // Enable timestamps
    public $timestamps = true;

    // Use custom timestamp column names
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    // Optional: specify table name (if it's not plural or different)
    protected $table = 'staff';

    // Fillable fields for mass-assignment
    protected $fillable = [
        'eng_name',
        'jp_name',
        'username',
        'password',
        'address',
        'ph_number',
        'position',
        'role',
        'email',
        'perment_date',
        'ref_person',
        'ref_ph_number',
        'project',
        'sort_key',
    ];

    // If you want password to be hidden in JSON responses
    protected $hidden = ['password'];
}
