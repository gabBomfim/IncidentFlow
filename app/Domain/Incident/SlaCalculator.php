<?php

namespace App\Domain\Incident;

use App\Models\Incident;
use Carbon\CarbonImmutable;

final class SlaCalculator
{
    public function for(Incident $incident): array
    {
        $elapsedMinutes = max(0, CarbonImmutable::parse($incident->created_at)->diffInMinutes(now()));
        $targetMinutes = $incident->severity->targetMinutes();
        $percentage = min(100, (int) round(($elapsedMinutes / $targetMinutes) * 100));
        $state = $elapsedMinutes >= $targetMinutes
            ? 'breached'
            : ($percentage >= 75 ? 'at_risk' : 'within_target');

        return [
            'state' => $state,
            'elapsed_minutes' => $elapsedMinutes,
            'target_minutes' => $targetMinutes,
            'percentage' => $percentage,
            'label' => match ($state) {
                'breached' => 'SLA breached',
                'at_risk' => 'Approaching SLA',
                default => 'Within target',
            },
        ];
    }
}
