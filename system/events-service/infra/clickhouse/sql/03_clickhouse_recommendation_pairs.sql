CREATE DATABASE IF NOT EXISTS trimiata_events;

CREATE TABLE IF NOT EXISTS trimiata_events.recommendation_pairs
(
    product_id UInt64,
    related_product_id UInt64,
    score Float64,
    reason LowCardinality(String),
    generated_at DateTime DEFAULT now()
)
ENGINE = ReplacingMergeTree(generated_at)
ORDER BY (product_id, related_product_id, reason);

