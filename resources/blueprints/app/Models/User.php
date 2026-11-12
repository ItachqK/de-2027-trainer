<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'fio',
        'login',
        'phone',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function firstName(): string
    {
        $parts = preg_split('/\s+/u', trim($this->fio)) ?: [];

        // «Иванова Мария Сергеевна» → «Мария», «Администратор портала» → «Администратор».
        return count($parts) >= 3 ? $parts[1] : ($parts[0] ?? $this->fio);
    }
}
