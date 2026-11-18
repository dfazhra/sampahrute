<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Location extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type',
        'district',
        'village',
        'address',
        'lat',
        'lng',
        'capacity',
        'fill',
        'days',
        'window',
        'officer_code',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'float',
            'lng' => 'float',
            'capacity' => 'float',
            'fill' => 'integer',
            'days' => 'array',
        ];
    }

    public function officer(): BelongsTo
    {
        return $this->belongsTo(Officer::class, 'officer_code', 'code');
    }
}
