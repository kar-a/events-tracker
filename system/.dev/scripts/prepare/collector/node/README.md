# Node.js collector (MVP)

## Endpoints
- `GET /health` — liveness
- `GET /ready` — readiness (пока всегда ok; позже — проверки ClickHouse/Redis)
- `POST /v1/events` — принять 1 событие (object)
- `POST /v1/events/batch` — принять список событий (array)

## Event contract
Collector валидирует и нормализует события по контракту (см. `src/domain/eventSchema.ts`):
- обязательные поля: `event_id`, `source`, `event_type`, `event_time`, `user_key`, `session_key`
- продуктовые события требуют `product_id`
- `search` требует `search_query`
- `recommendation_*` требуют `context_id`

Нормализация:
- `event_time` → UTC `YYYY-MM-DD HH:mm:ss`
- пустые строки → `null` для nullable полей
- `payload` всегда объект (для ClickHouse будет сериализован в `payload_json`)

## Запуск (в составе compose)
Сервис `collector` поднимается через `system/.dev/scripts/prepare/docker-compose.events.yml`.

Переменные окружения:
- `HTTP_HOST`, `HTTP_PORT`
- `CLICKHOUSE_HTTP_URL`, `CLICKHOUSE_DB`, `CLICKHOUSE_USER`, `CLICKHOUSE_PASSWORD`
- `REDIS_URL`

## Быстрый тест (curl)
```bash
curl -sS -X POST http://localhost:8081/v1/events \
  -H 'Content-Type: application/json' \
  -d '{
    "event_id": "manual-demo-1",
    "source": "web",
    "event_type": "product_view",
    "event_time": "2026-03-27T12:00:00Z",
    "user_key": "metrika:demo",
    "session_key": "web:session:demo",
    "product_id": 12345,
    "context_type": "product",
    "context_id": "12345",
    "page_url": "https://trimiata.ru/catalog/demo/",
    "page_type": "product",
    "payload": { "price": 189900, "currency": "RUB" }
  }'
```

## Debug
- Логи: `docker compose -f docker-compose.events.yml logs -f collector`
- Частая ошибка: `422` → смотри поле `errors[]` в ответе.
- Ошибки вставки в ClickHouse: смотри warn `clickhouse: insert failed` (там попытка/сообщение); при исчерпании ретраев collector вернёт `500`.

## Что дальше (план)
1) Реальный sink в ClickHouse: батч‑вставка `JSONEachRow` + ретраи. (СДЕЛАНО)
2) DLQ: запись невалидных событий в отдельную таблицу/файл.
3) Rate limiting + auth (например, HMAC header) для продакшена.

