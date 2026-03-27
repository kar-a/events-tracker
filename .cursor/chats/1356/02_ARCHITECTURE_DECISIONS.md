# Architecture decisions

## Final recommended architecture

```text
Website / Bitrix
    -> data.trimiata.ru collector
    -> ClickHouse (raw events)
    -> aggregate jobs
    -> Redis (serving cache)
    -> Bitrix recommendation endpoint / widget
```

External sources:
- Yandex Metrika Logs API -> importer -> ClickHouse
- Future AppMetrica / mobile app -> collector or importer -> ClickHouse

## Why a separate contour on data.trimiata.ru
Because the site CMS database should not become the central event warehouse.

Benefits:
- isolation from Bitrix transactional workload
- append-only event ingestion
- easier scaling
- easier integration of external sources
- independent deployment cycle

## Why ClickHouse
Use ClickHouse as the main raw event store because:
- it is built for high-ingest analytics
- it handles append-only event data well
- it is a natural fit for web event logs
- official Yandex Metrika docs explicitly mention storing Logs API data in ClickHouse
- it supports TTL, partitioning, materialized views, and fast aggregates

## Why Redis
Redis is the serving layer for ready recommendations:
- low-latency reads
- simple top-N storage by key
- avoids heavy computation on page render
- ideal for blocks like `rec:product:{id}` and `rec:user:{id}`

## Why not MySQL as the event warehouse
A current search-query table in MySQL already became slow around ~871k rows.  
The issue is not only row count. The deeper issue is architectural misuse:
- transactional DB used for event firehose
- long-term raw logs mixed with app data
- expensive filters / aggregates on row-store tables
- no clear append-only analytics design

## Queue / broker decision
Day 1:
- **no Kafka required**
- collector can buffer and batch inserts to ClickHouse

Later:
- add Kafka / Redpanda / NATS if throughput, fan-out, or resiliency needs grow

## Recommended service boundaries
### Collector service
- receive events
- validate schema
- normalize payload
- batch insert to ClickHouse
- optionally write failed events to dead-letter storage

### Import service
- import Yandex Metrika logs
- normalize to same schema
- write to ClickHouse

### Aggregation service
- compute item-to-item similarity
- compute popularity and fallback lists
- write top-N to Redis

### Serving service
- returns recommendation IDs / payload for frontend widgets
