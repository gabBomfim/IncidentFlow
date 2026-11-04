<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Incident\IncidentTransitionService;
use App\Enums\IncidentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIncidentRequest;
use App\Http\Requests\TransitionIncidentRequest;
use App\Http\Resources\IncidentResource;
use App\Models\Incident;
use DomainException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class IncidentController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $incidents = Incident::query()
            ->with('events')
            ->when(request('status'), fn ($query, $status) => $query->where('status', $status))
            ->when(request('severity'), fn ($query, $severity) => $query->where('severity', $severity))
            ->latest()
            ->get();

        return IncidentResource::collection($incidents);
    }

    public function store(StoreIncidentRequest $request): IncidentResource
    {
        $nextNumber = ((int) Incident::max('id')) + 2049;
        $incident = Incident::create([
            ...$request->validated(),
            'reference' => 'INC-'.str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT),
            'status' => IncidentStatus::Investigating,
        ]);

        $incident->events()->create([
            'type' => 'created',
            'message' => 'Incident created and assigned for investigation.',
        ]);

        return new IncidentResource($incident->load('events'));
    }

    public function show(Incident $incident): IncidentResource
    {
        return new IncidentResource($incident->load('events'));
    }

    public function transition(
        TransitionIncidentRequest $request,
        Incident $incident,
        IncidentTransitionService $transitions,
    ): IncidentResource {
        try {
            $updated = $transitions->transition(
                $incident,
                IncidentStatus::from($request->validated('status')),
            );
        } catch (DomainException $exception) {
            throw ValidationException::withMessages(['status' => $exception->getMessage()]);
        }

        return new IncidentResource($updated->load('events'));
    }
}
