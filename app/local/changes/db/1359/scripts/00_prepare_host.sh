#!/usr/bin/env bash
set -euo pipefail

echo "[1359] Prepare host for events.trimiata.ru"

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

if [[ ! -f "$ROOT_DIR/.env" ]]; then
	echo "[1359] .env not found. Copying from .env.example"
	cp "$ROOT_DIR/.env.example" "$ROOT_DIR/.env"
fi

source "$ROOT_DIR/.env"

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
sudo apt-get install -y curl jq gzip

if ! docker compose version >/dev/null 2>&1 && ! command -v docker-compose >/dev/null 2>&1; then
	echo "Docker Compose still not available. Install docker-compose or docker compose plugin."
	exit 1
fi

if [[ -z "${EVENTS_DATA_ROOT:-}" ]]; then
	echo "[1359] EVENTS_DATA_ROOT is empty. Set it in .env"
	exit 1
fi

mkdir -p "${EVENTS_DATA_ROOT}/clickhouse/data"
mkdir -p "${EVENTS_DATA_ROOT}/clickhouse/log"
mkdir -p "${EVENTS_DATA_ROOT}/redis/data"
mkdir -p "${EVENTS_DATA_ROOT}/backups"

echo "[1359] Host is prepared"
