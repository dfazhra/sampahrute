<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'title',
        'contact',
        'area',
        'officer_code',
        'active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
        ];
    }

    public function officer(): HasOne
    {
        return $this->hasOne(Officer::class, 'code', 'officer_code');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function toViewArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->role,
            'title' => $this->title ?? match ($this->role) {
                'admin' => 'Pengelola Lingkungan',
                'petugas' => 'Petugas Pengangkut',
                default => 'Warga '.($this->area ?: 'Surakarta'),
            },
            'contact' => $this->contact ?: ($this->email ?: '-'),
            'area' => $this->area ?: 'Surakarta',
            'officer_id' => $this->officer_code ?: 'PTG-01',
            'active' => (bool) $this->active,
        ];
    }
}
