# Architecture

MVP data-flow:
1) Producers → `collector-node` (HTTP)
2) Collector → validate+normalize via `packages/contract`
3) Collector → ClickHouse `events_raw` (append-only)
4) Jobs → aggregate → Redis (serving keys)
5) Grafana → explore ClickHouse

