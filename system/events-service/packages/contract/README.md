# packages/contract

Единая точка правды для event contract.

Состав:
- `schema/event.jsonschema.json` — нейтральная спецификация (для внешних интеграций)
- `node/` — Node bindings (Zod + normalize helpers)
- `python/` — Python bindings (каркас)
- `tests/vectors/` — тест-вектора (valid/invalid), которые должны одинаково обрабатываться в Node и Python

