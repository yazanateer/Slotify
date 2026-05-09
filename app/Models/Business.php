<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Service;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;



class Business extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
    'name',
    'email',
    'slug',
    'password',
    'role',
    'business_id',
    ];

    public function users() : HasMany
    {
        return $this->hasMany(User::class);
    }

    public function services() : HasMany
    {
        return $this->hasMany(Service::class);
    }

}
