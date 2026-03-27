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

if [[ -z "${EVENTS_DATA_ROOT:-}" ]]; then
	echo "[events-service] EVENTS_DATA_ROOT is empty. Set it in .env"
	exit 1
fi

BACKUP_DIR="${EVENTS_DATA_ROOT}/backups"
mkdir -p "$BACKUP_DIR"

STAMP="$(date +%Y%m%d_%H%M%S)"
OUT_FILE="${BACKUP_DIR}/events_raw_${STAMP}.sql.gz"

echo "[events-service] Backup events_raw -> ${OUT_FILE}"
docker exec trimiata-events-clickhouse clickhouse-client \
	--user "$CLICKHOUSE_USER" \
	--password "$CLICKHOUSE_PASSWORD" \
	--query "SELECT * FROM ${CLICKHOUSE_DB}.events_raw FORMAT TabSeparatedWithNamesAndTypes" \
	| gzip -c > "$OUT_FILE"

echo "[events-service] Backup done"
