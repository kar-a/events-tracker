#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

if [[ ! -f .env ]]; then
	echo "[1359] .env not found. Abort."
	exit 1
fi

source .env

echo "[1359] Insert demo event into trimiata_events.events_raw"
docker exec trimiata-events-clickhouse clickhouse-client \
	--user "$CLICKHOUSE_USER" \
	--password "$CLICKHOUSE_PASSWORD" \
	--connect_timeout 5 \
	--receive_timeout 20 \
	--send_timeout 20 \
	--query "
	INSERT INTO trimiata_events.events_raw
	(
		event_time, event_id, source, event_type,
		user_key, session_key, product_id,
		context_type, context_id, page_url, page_type,
		referrer, device_type, utm_source, utm_medium, utm_campaign,
		search_query, position, payload_json
	)
	VALUES
	(
		now(),
		'manual-demo-' || toString(now()),
		'web',
		'product_view',
		'metrika:demo-user',
		'web:session:demo',
		12345,
		'product',
		'12345',
		'https://trimiata.ru/catalog/demo/',
		'product',
		'https://yandex.ru',
		'desktop',
		'yandex',
		'organic',
		'',
		NULL,
		NULL,
		'{\"price\":189900,\"currency\":\"RUB\"}'
	)"

echo "[1359] Last rows:"
docker exec trimiata-events-clickhouse clickhouse-client \
	--user "$CLICKHOUSE_USER" \
	--password "$CLICKHOUSE_PASSWORD" \
	--connect_timeout 5 \
	--receive_timeout 20 \
	--send_timeout 20 \
	--query "SELECT event_time, event_type, product_id, user_key FROM trimiata_events.events_raw ORDER BY ingested_at DESC LIMIT 5"
