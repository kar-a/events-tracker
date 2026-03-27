#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR/infra/compose"

if docker compose version >/dev/null 2>&1; then
	DC="docker compose"
elif command -v docker-compose >/dev/null 2>&1; then
	DC="docker-compose"
else
	echo "[events-service] Docker Compose not found"
	exit 1
fi

if [[ ! -f .env ]]; then
	echo "[events-service] .env not found. Copying from .env.example"
	cp .env.example .env
fi

$DC -f docker-compose.events.yml --env-file .env up -d --build
$DC -f docker-compose.events.yml --env-file .env ps

echo "[events-service] Stack is up"
echo "[events-service] Grafana: http://localhost:${GRAFANA_PORT:-3000}"

