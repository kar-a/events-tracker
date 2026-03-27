ATTACH TABLE _ UUID '3a43920b-ab4f-4e59-bbf2-ebdeb2e44fce'
(
    `job_id` UUID,
    `source` LowCardinality(String),
    `external_request_id` String,
    `status` LowCardinality(String),
    `date_from` Date,
    `date_to` Date,
    `parts_total` UInt32 DEFAULT 0,
    `parts_downloaded` UInt32 DEFAULT 0,
    `started_at` DateTime DEFAULT now(),
    `finished_at` Nullable(DateTime),
    `meta_json` String DEFAULT '{}'
)
ENGINE = MergeTree
ORDER BY (source, started_at, job_id)
SETTINGS index_granularity = 8192
