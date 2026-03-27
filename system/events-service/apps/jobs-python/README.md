# jobs-python (skeleton)

Каркас будущих джобов импорта/агрегаций для контура событий.

Планируемые роли:
- Importer: внешние источники → нормализация → ClickHouse `events_raw`
- Aggregator: пары/популярность → Redis (serving keys)
- Ops: cron/k8s jobs, метрики, контроль `import_jobs`

