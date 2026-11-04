# Architecture

## Boundary map

~~~text
React board
    |
    | JSON over /api/v1
    v
IncidentController -> Form Requests -> Incident model
    |                                  |
    |                                  +--> IncidentEvent timeline
    v
IncidentTransitionService -> IncidentStatusChanged -> RecordIncidentTimeline
    |
    +--> SlaCalculator (pure response-time policy)
~~~

## Domain model

`Incident` owns the operational record: reference, service, severity, status, summary, owner, and resolution timestamp. `IncidentEvent` is append-only from the product's point of view and gives the UI a durable response timeline.

The transition service is the only place that changes a status through the API. The allowed graph is deliberately explicit:

- investigating -> identified or resolved
- identified -> monitoring or resolved
- monitoring -> identified or resolved
- resolved -> terminal

This keeps invalid workflow states out of controllers and makes the policy easy to unit test.

## SLA policy

`SlaCalculator` receives an incident and returns a serializable snapshot. It does not persist derived percentages, which means the clock stays current without a scheduled job. Severity maps to a target in minutes: 15, 30, 60, or 240.

At 75% of the target, an incident is `at_risk`; at the target, it is `breached`. Resolved incidents preserve their response history while the UI can continue to show the current snapshot for the record.

## Frontend boundary

The React app owns board filters, selection, form state, and refresh behavior. The server remains the authority for validation, transition rules, SLA calculation, and timeline creation. This keeps the local demo thin while leaving a clean path to authentication and live updates.
