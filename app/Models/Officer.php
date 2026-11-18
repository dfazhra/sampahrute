<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Officer extends Model
{
    protected $fillable = [
        'code',
        'user_id',
        'name',
        'phone',
        'area',
        'vehicle',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class, 'officer_code', 'code');
    }

    public function toViewArray(): array
    {
        return [
            'id' => $this->code,
            'name' => $this->name,
            'phone' => $this->phone ?? '-',
            'area' => $this->area ?? 'Surakarta',
            'vehicle' => $this->vehicle ?? 'Truk Pengangkut',
            'active' => (bool) $this->active,
        ];
    }
}
