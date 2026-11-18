<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PickupLog extends Model
{
    protected $fillable = [
        'code',
        'pickup_date',
        'location_code',
        'location_name',
        'district',
        'officer_code',
        'officer_name',
        'window',
        'actual_time',
        'volume',
        'result',
        'note',
        'photo',
    ];

    protected function casts(): array
    {
        return [
            'pickup_date' => 'date',
            'volume' => 'float',
        ];
    }
}
