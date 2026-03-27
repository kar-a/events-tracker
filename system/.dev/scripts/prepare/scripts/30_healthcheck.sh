#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

if [[ ! -f .env ]]; then
	echo "[1359] .env not found. Abort."
	exit 1
fi

source .env

echo "[1359] ClickHouse HTTP ping"
curl -fsS "http://127.0.0.1:${CLICKHOUSE_HTTP_PORT}/ping"
echo

echo "[1359] ClickHouse SQL ping"
docker exec trimiata-events-clickhouse clickhouse-client \
	--user "$CLICKHOUSE_USER" \
	--password "$CLICKHOUSE_PASSWORD" \
	--query "SELECT 1"

echo "[1359] Redis ping"
docker exec trimiata-events-redis redis-cli -a "$REDIS_PASSWORD" PING

echo "[1359] Healthcheck OK"
