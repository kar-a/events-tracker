# Storage stack review

## Requirement
The project needs one common event base that can later accept:
- site events
- Yandex Metrika imports
- future mobile app events
- possibly backend/CRM events

It must remain fast even with very large volumes.

## Recommended stack
### 1. ClickHouse — raw and aggregate analytics
Use for:
- raw event storage
- aggregate tables
- pair calculations
- popularity stats
- user/category affinity summaries

### 2. Redis — online serving
Use for:
- ready recommendation lists
- top products by category
- user recent-interest caches
- A/B assignment cache if useful

### 3. PostgreSQL or existing service DB — metadata only
Use for:
- import job status
- algorithm versions
- A/B experiment metadata
- operational settings

### 4. S3 / MinIO — archive
Use for:
- downloaded Metrika files
- replayable raw imports
- backup of original raw payloads if needed

## ClickHouse design notes
- use append-only inserts
- batch inserts; avoid many tiny writes
- partition by low-cardinality time unit (usually month; day only if justified)
- design `ORDER BY` for real query patterns
- use TTL for retention

## Suggested event lifecycle
1. website/app/import sends raw event
2. collector validates and normalizes it
3. event inserted into `events_raw`
4. aggregate jobs build recommendation inputs
5. Redis receives top-N recommendation outputs

## Why this stack is future-proof enough
- raw event scale goes to ClickHouse
- recommendation serving stays fast in Redis
- app/site database stays clean
- additional sources can join later without redesign
