<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $fillable = [
        'code',
        'user_id',
        'resident_name',
        'location_code',
        'officer_code',
        'title',
        'category',
        'incident_date',
        'description',
        'photo',
        'status',
        'priority',
        'reject_reason',
        'verified_at',
        'assigned_at',
        'completed_at',
        'rejected_at',
    ];

    protected function casts(): array
    {
        return [
            'incident_date' => 'date',
            'verified_at' => 'datetime',
            'assigned_at' => 'datetime',
            'completed_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_code', 'code');
    }

    public function officer(): BelongsTo
    {
        return $this->belongsTo(Officer::class, 'officer_code', 'code');
    }
}
