#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

if [[ ! -f .env ]]; then
	echo "[1359] .env not found. Abort."
	exit 1
fi

source .env

if [[ -z "${EVENTS_DATA_ROOT:-}" ]]; then
	echo "[1359] EVENTS_DATA_ROOT is empty. Set it in .env"
	exit 1
fi

BACKUP_DIR="${EVENTS_DATA_ROOT}/backups"
mkdir -p "$BACKUP_DIR"

STAMP="$(date +%Y%m%d_%H%M%S)"
OUT_FILE="${BACKUP_DIR}/events_raw_${STAMP}.sql.gz"

echo "[1359] Backup events_raw -> ${OUT_FILE}"
docker exec trimiata-events-clickhouse clickhouse-client \
	--user "$CLICKHOUSE_USER" \
	--password "$CLICKHOUSE_PASSWORD" \
	--query "SELECT * FROM trimiata_events.events_raw FORMAT TabSeparatedWithNamesAndTypes" \
| gzip -c > "$OUT_FILE"

echo "[1359] Backup done"
