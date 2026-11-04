<?php

namespace Tests\Unit\Domain;

use App\Domain\Incident\SlaCalculator;
use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use App\Models\Incident;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class SlaCalculatorTest extends TestCase
{
    public function test_it_marks_an_incident_within_target(): void
    {
        $sla = (new SlaCalculator)->for($this->incidentCreatedMinutesAgo(10, IncidentSeverity::Sev2));

        $this->assertSame('within_target', $sla['state']);
        $this->assertSame(30, $sla['target_minutes']);
        $this->assertSame(33, $sla['percentage']);
    }

    public function test_it_marks_an_incident_at_risk_before_breach(): void
    {
        $sla = (new SlaCalculator)->for($this->incidentCreatedMinutesAgo(24, IncidentSeverity::Sev2));

        $this->assertSame('at_risk', $sla['state']);
        $this->assertSame(80, $sla['percentage']);
    }

    public function test_it_caps_a_breached_incident_at_one_hundred_percent(): void
    {
        $sla = (new SlaCalculator)->for($this->incidentCreatedMinutesAgo(45, IncidentSeverity::Sev2));

        $this->assertSame('breached', $sla['state']);
        $this->assertSame(100, $sla['percentage']);
    }

    private function incidentCreatedMinutesAgo(int $minutes, IncidentSeverity $severity): Incident
    {
        $incident = new Incident([
            'severity' => $severity,
            'status' => IncidentStatus::Investigating,
        ]);
        $incident->created_at = CarbonImmutable::now()->subMinutes($minutes);

        return $incident;
    }
}
