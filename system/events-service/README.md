# events-service

Монорепозиторий сервиса событий Trimiata:
- **Node.js collector** (HTTP ingestion) → ClickHouse `events_raw`
- **Python jobs** (каркас) → агрегации/импорты → Redis (serving слой)
- **Shared contract** → единая точка правды для валидации/нормализации событий

## Структура

```
system/events-service/
  apps/
    collector-node/
    jobs-python/
  packages/
    contract/
  infra/
    compose/
    clickhouse/sql/
    grafana/
  scripts/
  docs/
```

## Быстрый старт (dev)

Инфраструктура и compose лежат в `infra/compose/`.

```bash
cd system/events-service/infra/compose
cp .env.example .env
docker compose -f docker-compose.events.yml --env-file .env up -d --build
```

## Контракт событий

См. `packages/contract/README.md` и тест-вектора в `packages/contract/tests/vectors/`.

