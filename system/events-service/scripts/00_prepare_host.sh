#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ENV_FILE="$ROOT_DIR/infra/compose/.env"

echo "[events-service] Prepare host (dirs + packages)"

if [[ ! -f "$ENV_FILE" ]]; then
	echo "[events-service] $ENV_FILE not found. Copying from .env.example"
	cp "$ROOT_DIR/infra/compose/.env.example" "$ENV_FILE"
fi

# shellcheck source=/dev/null
source "$ENV_FILE"

if ! command -v docker >/dev/null 2>&1; then
	echo "Docker not found. Installing docker.io..."
	sudo apt-get update
	sudo apt-get install -y docker.io
	sudo systemctl enable --now docker
fi

if ! docker compose version >/dev/null 2>&1; then
	echo "Docker Compose plugin not found. Installing..."
	sudo apt-get update
	sudo apt-get install -y docker-compose-plugin
fi

sudo apt-get update
sudo apt-get install -y ca-certificates curl gzip jq wget

if ! docker compose version >/dev/null 2>&1 && ! command -v docker-compose >/dev/null 2>&1; then
	echo "Docker Compose still not available."
	exit 1
fi

if [[ -z "${EVENTS_DATA_ROOT:-}" ]]; then
	echo "[events-service] EVENTS_DATA_ROOT is empty. Set it in $ENV_FILE"
	exit 1
fi

mkdir -p "${EVENTS_DATA_ROOT}/clickhouse/data"
mkdir -p "${EVENTS_DATA_ROOT}/clickhouse/log"
mkdir -p "${EVENTS_DATA_ROOT}/redis/data"
mkdir -p "${EVENTS_DATA_ROOT}/grafana/data"
mkdir -p "${EVENTS_DATA_ROOT}/backups"

if command -v chown >/dev/null 2>&1; then
	sudo chown -R 472:472 "${EVENTS_DATA_ROOT}/grafana" || true
fi

echo "[events-service] Host is prepared"
