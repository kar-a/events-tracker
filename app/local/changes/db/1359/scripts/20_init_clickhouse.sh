#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

cd "$ROOT_DIR"

if [[ ! -f .env ]]; then
	echo "[1359] .env not found. Abort."
	exit 1
fi

source .env

SQL_FILES=(
	"sql/01_clickhouse_events_raw.sql"
	"sql/02_clickhouse_import_jobs.sql"
	"sql/03_clickhouse_recommendation_pairs.sql"
)

for sql_file in "${SQL_FILES[@]}"; do
	echo "[1359] Apply ${sql_file}"
	docker exec -i trimiata-events-clickhouse clickhouse-client \
		--user "$CLICKHOUSE_USER" \
		--password "$CLICKHOUSE_PASSWORD" \
		--multiquery < "$sql_file"
done

echo "[1359] ClickHouse schema initialized"
