# IncidentFlow

IncidentFlow is an incident command center for small engineering teams. It turns an operational event into a visible response workflow: declare the incident, assign an owner, watch the SLA clock, move through valid states, and preserve the timeline for learning.

## Why it exists

Many incident tools optimize for storage or alert volume. IncidentFlow is intentionally smaller and opinionated around the moments that make a response accountable:

- every incident has a severity-specific response target;
- every status change is validated by a domain transition service;
- every meaningful change is appended to a timeline;
- the board makes risk visible without hiding the underlying incident detail.

## Stack

- Laravel 13 and PHP 8.3
- React 19, TypeScript, Vite 8
- SQLite for a zero-friction local demo
- Redis 7 through Docker Compose for local infrastructure parity
- Laravel Sanctum installed for the next authenticated API boundary
- PHPUnit and Laravel Pint

## Run locally

~~~text
cd ~/engineering-portfolio/IncidentFlow
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed

source ~/.nvm/nvm.sh
nvm use 22.23.2
npm install
npm run dev
~~~

Open [http://127.0.0.1:8012](http://127.0.0.1:8012) after starting Laravel in a second shell:

~~~text
php artisan serve --host=127.0.0.1 --port=8012
~~~

Optional Redis service:

~~~text
docker compose up -d
~~~

The application remains runnable with SQLite and database-backed queue/cache defaults, so the local UI does not depend on Docker.

## API surface

| Method | Endpoint | Purpose |
| --- | --- | --- |
| GET | `/api/v1/incidents` | List incidents, with optional `status` and `severity` filters |
| POST | `/api/v1/incidents` | Declare a new incident in `investigating` |
| GET | `/api/v1/incidents/{incident}` | Inspect one incident and its timeline |
| PATCH | `/api/v1/incidents/{incident}/status` | Apply a validated status transition |

## Engineering notes

The API response includes a computed SLA object with elapsed minutes, target minutes, percentage, state, and a human-readable label. Incident statuses are not free-form: the domain service rejects transitions that do not belong to the response lifecycle.

The board is a focused operational surface rather than a generic admin template. The geometric signal mark, charcoal palette, orange incident accents, and lime health states are designed to make urgency and ownership scannable.

## Verification

~~~text
php artisan test
./vendor/bin/pint --test
npm run build
~~~

Architecture decisions and tradeoffs live in [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) and [docs/DECISIONS.md](docs/DECISIONS.md).
