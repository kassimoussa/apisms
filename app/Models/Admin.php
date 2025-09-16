<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Admin extends Model
{
    protected $fillable = [
        'name',
        'email', 
        'username',
        'password',
        'role',
        'active',
        'last_login_at'
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    public function isActive(): bool
    {
        return $this->active;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function setPassword(string $password): void
    {
        $this->password = Hash::make($password);
        $this->save();
    }

    public function checkPassword(string $password): bool
    {
        return Hash::check($password, $this->password);
    }

    public function updateLastLogin(): void
    {
        $this->last_login_at = now();
        $this->save();
    }

    public static function findByUsername(string $username): ?Admin
    {
        return static::where('username', $username)
                    ->where('active', true)
                    ->first();
    }

    public static function findByEmail(string $email): ?Admin
    {
        return static::where('email', $email)
                    ->where('active', true)
                    ->first();
    }
}
