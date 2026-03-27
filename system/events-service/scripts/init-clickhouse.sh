#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR/infra/compose"

if [[ ! -f .env ]]; then
	echo "[events-service] .env not found. Abort."
	exit 1
fi

# shellcheck source=/dev/null
source .env

SQL_FILES=(
	"../clickhouse/sql/01_clickhouse_events_raw.sql"
	"../clickhouse/sql/02_clickhouse_import_jobs.sql"
	"../clickhouse/sql/03_clickhouse_recommendation_pairs.sql"
)

for sql_file in "${SQL_FILES[@]}"; do
	echo "[events-service] Apply ${sql_file}"
	docker exec -i trimiata-events-clickhouse clickhouse-client \
		--user "$CLICKHOUSE_USER" \
		--password "$CLICKHOUSE_PASSWORD" \
		--multiquery < "$sql_file"
done

echo "[events-service] ClickHouse schema initialized"

