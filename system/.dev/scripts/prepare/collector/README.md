# Collector (events)

Цель: единая «дверь» приёма событий для 1356 (персональные рекомендации) — `POST /v1/events` и `POST /v1/events/batch`.

## Архитектура и узлы системы

### Поток данных (MVP)
1) Сайт/импорт/будущие источники отправляют события → `collector` (HTTP).
2) Collector валидирует и нормализует payload по единому контракту.
3) Collector пишет события в ClickHouse `events_raw` (append-only).
4) Отдельные джобы агрегации строят пары/топ‑N → Redis (serving слой).

### Почему два языка
- **Node.js**: быстрый HTTP ingestion, удобная работа с JSON, высокая скорость разработки, хорошая экосистема логирования/observability.
- **Python (каркас)**: будущие джобы импорта/агрегации (например, Metrika Logs API, pair‑calc, загрузка в Redis). Сейчас — только структура, без реализации.

## Структура приложения (app)

```
collector/
  node/                      # HTTP collector (реальная реализация)
    src/
      app/                   # bootstrap: config, logger, server
      http/                  # routes/controllers
      domain/                # event contract + normalization
      sinks/                 # ClickHouse/Redis interfaces + stubs
      utils/                 # small helpers
    Dockerfile
    package.json
    tsconfig.json
    README.md

  python/                    # каркас будущего worker/aggregator
    src/trimiata_events/
      app/
      domain/
      sinks/
    pyproject.toml
    README.md
```

## Использование
- Node‑collector поднимается через `docker-compose.events.yml` как сервис `collector`.
- Порты и креды берутся из `.env` рядом с compose (`CLICKHOUSE_*`, `REDIS_*`, `COLLECTOR_PORT`).

## Debug
- Логи collector: `docker compose -f docker-compose.events.yml logs -f collector`
- Проверка здоровья: `GET /health`, `GET /ready`
- Пробная отправка события: см. `collector/node/README.md` (curl examples)

