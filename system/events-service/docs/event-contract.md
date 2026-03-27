# Event contract

Источник правды:

- **JSON Schema**: `packages/contract/schema/event.jsonschema.json`
- **Node (валидация + нормализация)**: `packages/contract/node/src/zod.ts`, публичный API — `packages/contract/node/src/index.ts`
- **Тест-вектора**: `packages/contract/tests/vectors/valid/` и `invalid/`

Коллектор импортирует пакет `@trimiata/events-contract` (см. `apps/collector-node/package.json`, зависимость `file:../../packages/contract/node`).

## Обязательные поля (база)

`event_id`, `source`, `event_type`, `event_time`, `user_key`, `session_key`.

## Дополнительные правила по типу события

- События с товарами (`product_view`, `add_to_cart`, `remove_from_cart`, `purchase`) — нужен `product_id`.
- `search` — нужен `search_query`.
- `recommendation_show`, `recommendation_click` — нужен `context_id`.

Подробности реализации — в `normalizeEvent()` в `zod.ts`.
