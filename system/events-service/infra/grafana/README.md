# Grafana (Trimiata events)

## Purpose
Grafana поднимается вместе с ClickHouse и автоматически:
- подключает ClickHouse datasource;
- загружает стартовый dashboard `Trimiata events — overview`.

## Access
- URL: `http://localhost:${GRAFANA_PORT}`
- Логин/пароль: `${GRAFANA_ADMIN_USER}` / `${GRAFANA_ADMIN_PASSWORD}`

