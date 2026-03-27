# events-service

Продукт для домена **events.trimiata.ru** (без Bitrix и без каталога `app/` в составе этой поставки). Публичный сайт **trimiata.ru** живёт отдельно и ходит сюда только по HTTP с телом событий по общему контракту.

Монорепозиторий сервиса событий Trimiata:
- **Node.js collector** (HTTP ingestion) → ClickHouse `events_raw`
- **Python jobs** (каркас) → агрегации/импорты → Redis (serving слой)
- **Shared contract** (`packages/contract`) → единая точка правды для валидации/нормализации событий

## Структура

Полное дерево и правила размещения кода — **`docs/STRUCTURE.md`**.

Кратко:

```
system/events-service/
  Makefile, docker-compose.yml, .env.example (указатель)
  apps/{collector-node,jobs-python}
  packages/contract
  infra/{compose,clickhouse,grafana,nginx,docker}
  scripts/
  docs/
```

## Быстрый старт (dev)

**Вариант A — Makefile из корня** (нужен `make` и созданный `infra/compose/.env`):

```bash
cd system/events-service
cp infra/compose/.env.example infra/compose/.env
make up
```

**Вариант B — только compose** (как раньше):

```bash
cd system/events-service/infra/compose
cp .env.example .env
docker compose -f docker-compose.events.yml --env-file .env up -d --build
```

**Вариант C — корневой `docker-compose.yml`** (если плагин поддерживает `include`):

```bash
cd system/events-service
docker compose --env-file infra/compose/.env up -d --build
```

## Документация

- Каталоги и конвенции: **`docs/STRUCTURE.md`**
- Архитектура: `docs/architecture.md`
- Контракт: `docs/event-contract.md`
- Деплой: `docs/deployment.md`
- Отладка: `docs/debugging.md`
- Сравнение с альтернативной «плоской» схемой: `docs/structure-comparison.md`

## Контракт событий

См. `packages/contract/README.md` и тест-вектора в `packages/contract/tests/vectors/`.

