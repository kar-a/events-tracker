# 1359: events.trimiata.ru — контур событий (архив задачи)

Инфраструктура и скрипты вынесены в монорепозиторий **`system/events-service/`**.

- Compose, ClickHouse SQL, Grafana, collector: `system/events-service/infra/`
- Скрипты (`00_prepare_host`, `10_up`, `20_init_clickhouse`, healthcheck, demo, backup): `system/events-service/scripts/`
- Быстрый старт: `system/events-service/README.md`

Не дублируйте compose и SQL здесь — единственный источник правды указан выше.
