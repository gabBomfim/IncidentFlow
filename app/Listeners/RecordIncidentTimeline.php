<?php

namespace App\Listeners;

use App\Events\IncidentStatusChanged;
use App\Models\IncidentEvent;

class RecordIncidentTimeline
{
    public function handle(IncidentStatusChanged $event): void
    {
        IncidentEvent::create([
            'incident_id' => $event->incident->id,
            'type' => 'status_change',
            'message' => "Status changed from {$event->from} to {$event->to}.",
            'metadata' => ['from' => $event->from, 'to' => $event->to],
        ]);
    }
}
