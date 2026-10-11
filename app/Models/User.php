<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// Spatie RBAC
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',

        'is_owner',
        'telegram_chat_id',
        'telegram_is_group',
        'ownership_transferred_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',

        'is_owner' => 'boolean',
        'telegram_is_group' => 'boolean',
        'ownership_transferred_at' => 'datetime',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'placed_by');
    }

    public function stockLogs()
    {
        return $this->hasMany(StockLog::class);
    }

    public function appNotifications()
    {
        return $this->hasMany(AppNotification::class);
    }
}
