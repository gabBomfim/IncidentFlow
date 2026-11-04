<?php

namespace App\Http\Resources;

use App\Domain\Incident\SlaCalculator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncidentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $this->loadMissing('events');

        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'title' => $this->title,
            'service' => $this->service,
            'severity' => $this->severity->value,
            'severity_label' => $this->severity->label(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'summary' => $this->summary,
            'owner' => $this->owner,
            'created_at' => $this->created_at?->toIso8601String(),
            'resolved_at' => $this->resolved_at?->toIso8601String(),
            'sla' => (new SlaCalculator)->for($this->resource),
            'timeline' => $this->events->map(fn ($event) => [
                'id' => $event->id,
                'type' => $event->type,
                'message' => $event->message,
                'created_at' => $event->created_at?->toIso8601String(),
            ])->values(),
        ];
    }
}
