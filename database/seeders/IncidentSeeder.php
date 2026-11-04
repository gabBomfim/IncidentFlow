<?php

namespace Database\Seeders;

use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use App\Models\Incident;
use Illuminate\Database\Seeder;

class IncidentSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['INC-2048', 'Elevated checkout latency', 'Checkout API', IncidentSeverity::Sev2, IncidentStatus::Investigating, 'p95 latency crossed the alert threshold after a dependency rollout.', 'Maya Chen', 18],
            ['INC-2047', 'Webhook delivery delays', 'HookRelay', IncidentSeverity::Sev3, IncidentStatus::Identified, 'Delivery attempts are slower than the normal baseline in one region.', 'Andre Silva', 42],
            ['INC-2046', 'Search error rate increased', 'Search', IncidentSeverity::Sev2, IncidentStatus::Monitoring, 'A rollback reduced errors; the team is monitoring recovery.', 'Priya Shah', 25],
            ['INC-2045', 'Certificate renewal reminder', 'Public API', IncidentSeverity::Sev4, IncidentStatus::Resolved, 'Certificate was renewed before customer impact.', 'Liam Osei', 180],
            ['INC-2044', 'Queue worker saturation', 'Billing jobs', IncidentSeverity::Sev3, IncidentStatus::Resolved, 'Workers were scaled and the backlog returned to normal.', 'Maya Chen', 120],
        ];

        foreach ($data as [$reference, $title, $service, $severity, $status, $summary, $owner, $minutesAgo]) {
            $incident = Incident::updateOrCreate(
                ['reference' => $reference],
                compact('reference', 'title', 'service', 'severity', 'status', 'summary', 'owner')
            );
            $incident->created_at = now()->subMinutes($minutesAgo);
            $incident->resolved_at = $status === IncidentStatus::Resolved ? now()->subMinutes($minutesAgo - 20) : null;
            $incident->save();

            $incident->events()->delete();
            $incident->events()->create(['type' => 'created', 'message' => 'Incident created and assigned for investigation.', 'created_at' => $incident->created_at]);
            $incident->events()->create(['type' => 'update', 'message' => $status === IncidentStatus::Resolved ? 'Resolution verified and incident closed.' : 'On-call owner posted the latest operational update.', 'created_at' => now()->subMinutes(max(2, (int) ($minutesAgo / 2)))]);
        }
    }
}
