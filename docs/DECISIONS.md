# Decisions

## SQLite first, Redis available

IncidentFlow uses SQLite as the default local database so a reviewer can run the project with PHP and Node alone. Docker Compose provides Redis on an isolated host port (`6381`) for queue/cache experiments and a more production-like development setup.

## Explicit transition service

A controller should coordinate HTTP concerns, not encode incident policy. `IncidentTransitionService` owns the status graph and emits a domain event after a successful change.

## Timeline through an event listener

Timeline persistence is separate from the transition service. The service emits `IncidentStatusChanged`, and `RecordIncidentTimeline` translates it into an `IncidentEvent`. Laravel's event discovery keeps the registration convention-based and avoids manual duplicate listener wiring.

## Computed SLA, not a cron job

The response clock is derived from `created_at` on read. This avoids stale percentage fields and removes a background process from the demo. If the product later needs historical SLA snapshots or pause windows, that policy can be introduced behind the calculator boundary.

## Local-only until approved

This project is intentionally prepared for review locally first. No remote repository or deployment is created as part of the local build phase.
