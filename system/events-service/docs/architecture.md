# Architecture

## Поток данных (MVP)

1. Producers → `apps/collector-node` (HTTP)
2. Collector → validate + normalize через `packages/contract`
3. Collector → ClickHouse `events_raw` (append-only)
4. Jobs (`apps/jobs-python`) → агрегации / импорты → Redis (serving)
5. Grafana → запросы к ClickHouse

## Почему так разложено по папкам

| Папка | Назначение |
|-------|------------|
| `apps/` | Запускаемые приложения (collector, python jobs) |
| `packages/contract` | Общий контракт событий (Node + schema + vectors) |
| `infra/` | Compose, ClickHouse DDL (`infra/clickhouse/sql`), Grafana, Nginx, справка по Docker |
| `scripts/` | Операционные shell-скрипты |

Соглашения по каталогам: **`docs/STRUCTURE.md`**. Подробное сравнение с «плоской» альтернативой: `structure-comparison.md`.

