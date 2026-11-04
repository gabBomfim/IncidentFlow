<?php

namespace Tests\Feature\Api;

use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use App\Models\Incident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncidentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_incidents_with_sla_data(): void
    {
        Incident::create($this->attributes('INC-3001', IncidentStatus::Investigating));
        Incident::create($this->attributes('INC-3002', IncidentStatus::Resolved));

        $this->getJson('/api/v1/incidents')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure(['data' => [['reference', 'status', 'sla', 'timeline']]]);
    }

    public function test_it_declares_an_incident_and_records_the_creation_event(): void
    {
        $response = $this->postJson('/api/v1/incidents', [
            'title' => 'Elevated checkout latency',
            'service' => 'Checkout API',
            'severity' => 'sev2',
            'owner' => 'Maya Chen',
            'summary' => 'P95 latency crossed the response threshold.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'investigating')
            ->assertJsonPath('data.timeline.0.type', 'created');
        $this->assertDatabaseHas('incidents', ['title' => 'Elevated checkout latency']);
    }

    public function test_it_transitions_an_incident_and_appends_to_timeline(): void
    {
        $incident = Incident::create($this->attributes('INC-3003', IncidentStatus::Investigating));
        $incident->events()->create(['type' => 'created', 'message' => 'Incident created.']);

        $this->patchJson('/api/v1/incidents/'.$incident->id.'/status', ['status' => 'identified'])
            ->assertOk()
            ->assertJsonPath('data.status', 'identified')
            ->assertJsonCount(2, 'data.timeline');
    }

    private function attributes(string $reference, IncidentStatus $status): array
    {
        return [
            'reference' => $reference,
            'title' => 'Test incident '.$reference,
            'service' => 'Test service',
            'severity' => IncidentSeverity::Sev2,
            'status' => $status,
            'summary' => 'A test incident for API coverage.',
            'owner' => 'Test owner',
        ];
    }
}
