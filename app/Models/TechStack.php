<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TechStack extends Model
{
    protected $table = 'tech_stacks';

    protected $fillable = [
        'name',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
