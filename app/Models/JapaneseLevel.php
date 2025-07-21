<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JapaneseLevel extends Model
{
    protected $table = 'japanese_levels';

    protected $fillable = [
        'name',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
