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

echo "[events-service] ClickHouse HTTP ping"
curl -fsS "http://127.0.0.1:${CLICKHOUSE_HTTP_PORT}/ping"
echo

echo "[events-service] ClickHouse SQL ping"
docker exec trimiata-events-clickhouse clickhouse-client \
	--user "$CLICKHOUSE_USER" \
	--password "$CLICKHOUSE_PASSWORD" \
	--query "SELECT 1"

echo "[events-service] Redis ping"
docker exec trimiata-events-redis redis-cli -a "$REDIS_PASSWORD" PING

echo "[events-service] Healthcheck OK"
