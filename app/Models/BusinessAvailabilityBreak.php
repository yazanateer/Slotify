<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use App\Models\Business;

class BusinessAvailabilityBreak extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'business_id',
        'day_of_week',
        'date',
        'start_time',
        'end_time',
    ];
}
