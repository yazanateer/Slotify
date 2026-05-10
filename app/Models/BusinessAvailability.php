<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use App\Models\Business;

class BusinessAvailability extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'business_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_active',
    ];

    public function business() : BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
