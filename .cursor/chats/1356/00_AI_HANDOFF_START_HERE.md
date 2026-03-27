# Trimiata — AI handoff for recommendation system

This folder is a compressed knowledge handoff for Cursor / AI to continue implementation of a personal recommendation system for **trimiata.ru**.

## Project snapshot
- Site: **trimiata.ru**
- Domain: jewelry e-commerce, gold products
- Stack: **PHP + 1C-Bitrix**
- Scale now:
  - ~500–1000 visitors/day
  - ~1000–1500 active products
- Reality:
  - most users are **anonymous**
  - Yandex Metrika has been installed for a long time
  - viewed product IDs are already stored in cookies
- Main business task:
  - launch a fast **MVP block “Вам может понравиться”**
  - then evolve into a strong personalized recommender

## Hard decisions already made
1. **Do not store the event firehose in Bitrix MySQL.**
2. Build a separate event collection contour on **`data.trimiata.ru`**.
3. Store raw events in **ClickHouse**.
4. Store ready-to-serve recommendations in **Redis**.
5. Use **Yandex Metrika ClientID** as the main anonymous user key when available.
6. Import historical data via **Yandex Metrika Logs API**.
7. Start with **item-to-item recommendations** based on views/cart/purchases.
8. Use fallback logic for cold users and cold products.
9. Measure effect through `show -> click -> add_to_cart -> purchase`.

## What to read first
1. `01_PROJECT_CONTEXT_AND_GOALS.md`
2. `02_ARCHITECTURE_DECISIONS.md`
3. `03_IMPLEMENTATION_PLAN.md`
4. `04_EVENT_CONTRACT.md`
5. `05_STORAGE_STACK_REVIEW.md`
6. `06_MVP_RECOMMENDATION_LOGIC.md`
7. `07_METRIKA_INTEGRATION.md`
8. `sql/01_clickhouse_events_raw.sql`
9. `code/TrimiataEventSchema.php`

## Critical constraints
- Recommendation serving must be **fast**.
- Event data must be **append-only**.
- Source systems must be normalized into **one event contract**.
- MVP must be realistic for current traffic and team size.
- Do not over-engineer with Kafka at day 1.

## Current recommended next implementation steps
1. Finalize event contract.
2. Implement `POST /v1/events` collector on `data.trimiata.ru`.
3. Create ClickHouse `events_raw`.
4. Start sending live site events.
5. Implement Metrika Logs API importer.
6. Build first item-to-item aggregates.
7. Load top-N recommendations into Redis.
8. Add Bitrix endpoint and widget for “Вам может понравиться”.
