# Suggested context prompt for Cursor / AI

Use this as a starting instruction for an AI assistant inside Cursor.

---

You are continuing a real implementation for trimiata.ru, a PHP + Bitrix jewelry e-commerce store.

Your task is to help build a production-grade recommendation system with an MVP block “Вам может понравиться”.

Important project constraints:
- most users are anonymous
- Yandex Metrika has existed for a long time
- event ingestion must be moved out of Bitrix MySQL
- the agreed architecture is:
  - data.trimiata.ru collector
  - ClickHouse for raw events
  - Redis for serving recommendations
  - Bitrix as the consumer of recommendation results
- use Yandex Metrika ClientID as primary anonymous key when available
- historical behavior should be imported via Metrika Logs API
- MVP recommendation logic is item-to-item based on views/cart/purchases
- recommendation serving must be precomputed and low-latency
- follow existing project coding style where possible

When generating code:
- prefer practical implementation over abstract theory
- keep backward compatibility in mind
- avoid over-engineering
- explain tradeoffs briefly
- output production-usable code whenever asked
- preserve the event contract from `code/TrimiataEventSchema.php`
- preserve the helper style around Yandex integrations, including reuse of the existing `getService(...)` pattern

Current priority:
1. implement collector
2. create ClickHouse schema
3. implement Metrika importer
4. compute item-to-item aggregates
5. serve recommendations to Bitrix

---
