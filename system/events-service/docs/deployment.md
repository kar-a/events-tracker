# Deployment

## Переменные

Шаблон: `infra/compose/.env.example`. Скопируй в `infra/compose/.env` и выставь:

- `EVENTS_DATA_ROOT` — абсолютный путь к данным на хосте (ClickHouse, Redis, Grafana). Пример: `.../system/events-service/data`.
- `CLICKHOUSE_*`, `REDIS_*`, `GRAFANA_*`, `COLLECTOR_PORT`.

Секреты в VCS не коммитим.

## Запуск стека

Из корня `events-service`:

```bash
make up
```

Или вручную:

```bash
cd infra/compose
cp -n .env.example .env
docker compose -f docker-compose.events.yml --env-file .env up -d --build
```

Если корневой `docker-compose.yml` с `include` не поддерживается вашей версией Compose — используйте только `infra/compose/docker-compose.events.yml`.

## Данные на диске

Тома привязаны к `${EVENTS_DATA_ROOT}`:

- `clickhouse/data`, `clickhouse/log`
- `redis/data`
- `grafana/data`

Перед первым запуском Grafana на Linux обычно нужно: `chown -R 472:472 "${EVENTS_DATA_ROOT}/grafana"`.

## Reverse proxy

Пример Nginx: `infra/nginx/events.trimiata.ru.conf.example` (переименуй и подставь пути/сертификаты).
