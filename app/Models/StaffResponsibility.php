<?php

namespace App\Models;

use App\Models\Staff;
use App\Models\Responsibility;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class StaffResponsibility extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    public $timestamps = true;

    protected $table = 'staff_responsibilities';

    protected $fillable = [
        'staff_id',
        'responsibility_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'id');
    }
    public function responsibility(): BelongsTo
    {
        return $this->belongsTo(Responsibility::class, 'responsibility_id', 'id');
    }
}
