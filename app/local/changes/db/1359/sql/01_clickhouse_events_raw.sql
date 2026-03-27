CREATE DATABASE IF NOT EXISTS trimiata_events;

CREATE TABLE IF NOT EXISTS trimiata_events.events_raw
(
    event_date Date DEFAULT toDate(event_time),
    event_time DateTime,
    event_id String,
    source LowCardinality(String),
    event_type LowCardinality(String),

    user_key String,
    session_key String,

    product_id UInt64 DEFAULT 0,
    context_type LowCardinality(Nullable(String)),
    context_id Nullable(String),

    page_url Nullable(String),
    page_type LowCardinality(Nullable(String)),
    referrer Nullable(String),
    device_type LowCardinality(Nullable(String)),

    utm_source Nullable(String),
    utm_medium Nullable(String),
    utm_campaign Nullable(String),

    search_query Nullable(String),
    position Nullable(UInt32),

    payload_json String DEFAULT '{}',
    ingested_at DateTime DEFAULT now()
)
ENGINE = MergeTree
PARTITION BY toYYYYMM(event_date)
ORDER BY (event_date, event_type, product_id, user_key, event_time, event_id)
TTL event_time + INTERVAL 18 MONTH DELETE;
