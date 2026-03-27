# Debugging

## Логи контейнеров

```bash
cd infra/compose
docker compose -f docker-compose.events.yml --env-file .env logs -f collector
docker compose -f docker-compose.events.yml --env-file .env logs -f clickhouse
docker compose -f docker-compose.events.yml --env-file .env logs -f grafana
```

## Collector

- Liveness: `GET http://127.0.0.1:${COLLECTOR_PORT}/health`
- Readiness: `GET /ready`
- Ingest: `POST /v1/events`, `POST /v1/events/batch`

Ответ `422` — смотри массив `errors` в JSON.

## ClickHouse

Внутри контейнера (подставь пользователя/пароль из `.env`):

```bash
docker exec -it trimiata-events-clickhouse clickhouse-client \
  --user "$CLICKHOUSE_USER" --password "$CLICKHOUSE_PASSWORD" \
  --query "SELECT count() FROM ${CLICKHOUSE_DB}.events_raw"
```

## Контракт (локально, без Docker)

```bash
cd packages/contract/node
npm install && npm run build && npm run test
```
