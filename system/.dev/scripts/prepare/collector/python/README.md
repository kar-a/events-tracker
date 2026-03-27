# Python (skeleton)

Этот каталог — каркас будущей python‑части (импорт/агрегации/загрузка рекомендаций).

## Планируемые роли
- Importer: Yandex Metrika Logs API → нормализация → ClickHouse `events_raw`
- Aggregator: пары/популярность → Redis (serving keys)
- Ops: cron/планировщик, метрики, контроль import_jobs

## Почему пока без реализации
На этом шаге задача — поднять ingestion door (Node collector) и стабилизировать контур.
Python‑часть добавляется, когда:
- определены источники импорта (Metrika),
- зафиксированы ретеншн/частоты джобов,
- согласован формат serving keys в Redis.

