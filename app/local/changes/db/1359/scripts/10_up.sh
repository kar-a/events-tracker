#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

cd "$ROOT_DIR"

if docker compose version >/dev/null 2>&1; then
	DC="docker compose"
elif command -v docker-compose >/dev/null 2>&1; then
	DC="docker-compose"
else
	echo "[1359] Docker Compose not found. Run scripts/00_prepare_host.sh"
	exit 1
fi

if [[ ! -f .env ]]; then
	echo "[1359] .env not found. Copying from .env.example"
	cp .env.example .env
fi

$DC -f docker-compose.events.yml --env-file .env up -d
$DC -f docker-compose.events.yml --env-file .env ps

echo "[1359] Stack is up"
