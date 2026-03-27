# collector-node

Node.js HTTP ingestion сервис для событий.

## Endpoints
- `GET /health` — liveness
- `GET /ready` — readiness
- `POST /v1/events` — 1 событие (object)
- `POST /v1/events/batch` — список событий (array)

## Контракт
Валидация/нормализация берётся из `@trimiata/events-contract` (workspace).

