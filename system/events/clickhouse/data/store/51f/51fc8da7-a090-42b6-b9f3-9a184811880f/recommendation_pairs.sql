ATTACH TABLE _ UUID '60c63cbb-f08e-48a9-a54a-0d809d1bc80c'
(
    `product_id` UInt64,
    `related_product_id` UInt64,
    `score` Float64,
    `reason` LowCardinality(String),
    `generated_at` DateTime DEFAULT now()
)
ENGINE = ReplacingMergeTree(generated_at)
ORDER BY (product_id, related_product_id, reason)
SETTINGS index_granularity = 8192
