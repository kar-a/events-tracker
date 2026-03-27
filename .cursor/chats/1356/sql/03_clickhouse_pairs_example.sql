/*
Example pair calculation idea for co-view.
Adapt and optimize for your actual data shape and batch strategy.
*/

CREATE TABLE IF NOT EXISTS recommendation_pairs
(
    product_id UInt64,
    related_product_id UInt64,
    score Float64,
    reason LowCardinality(String),
    generated_at DateTime DEFAULT now()
)
ENGINE = ReplacingMergeTree(generated_at)
ORDER BY (product_id, related_product_id, reason);

INSERT INTO recommendation_pairs
SELECT
    a.product_id AS product_id,
    b.product_id AS related_product_id,
    count() * 1.0 AS score,
    'co_view' AS reason,
    now() AS generated_at
FROM events_raw a
INNER JOIN events_raw b
    ON a.session_key = b.session_key
   AND a.product_id != b.product_id
WHERE a.event_type = 'product_view'
  AND b.event_type = 'product_view'
  AND a.event_time >= now() - INTERVAL 30 DAY
  AND a.product_id > 0
  AND b.product_id > 0
GROUP BY
    a.product_id,
    b.product_id
HAVING score >= 2;
