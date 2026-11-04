<?php

namespace App\Models;

use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Incident extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'title',
        'service',
        'severity',
        'status',
        'summary',
        'owner',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'severity' => IncidentSeverity::class,
            'status' => IncidentStatus::class,
            'resolved_at' => 'datetime',
        ];
    }

    public function events(): HasMany
    {
        return $this->hasMany(IncidentEvent::class)->latest();
    }
}
