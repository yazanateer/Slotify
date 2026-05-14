<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use App\Models\Business;

class BusinessDateOverride extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'business_id',
        'date',
        'is_active',
        'start_time',
        'end_time'
    ];
}
