<?php

namespace App\Domain\Incident;

use App\Enums\IncidentStatus;
use App\Events\IncidentStatusChanged;
use App\Models\Incident;
use DomainException;

final class IncidentTransitionService
{
    private const TRANSITIONS = [
        'investigating' => ['identified', 'resolved'],
        'identified' => ['monitoring', 'resolved'],
        'monitoring' => ['identified', 'resolved'],
        'resolved' => [],
    ];

    public function transition(Incident $incident, IncidentStatus $next): Incident
    {
        $current = $incident->status->value;

        if (! in_array($next->value, self::TRANSITIONS[$current], true)) {
            throw new DomainException("Cannot transition an incident from {$current} to {$next->value}.");
        }

        $incident->forceFill([
            'status' => $next,
            'resolved_at' => $next === IncidentStatus::Resolved ? now() : null,
        ])->save();

        IncidentStatusChanged::dispatch($incident, $current, $next->value);

        return $incident->refresh();
    }
}
