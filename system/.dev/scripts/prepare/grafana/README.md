# Grafana (Trimiata events)

## Purpose
Grafana поднимается вместе с ClickHouse в контуре `system\.dev\scripts\prepare` и автоматически:
- подключает ClickHouse datasource;
- загружает стартовый dashboard `Trimiata events — overview`.

## Access
- URL: `http://localhost:${GRAFANA_PORT}`
- Логин/пароль: `${GRAFANA_ADMIN_USER}` / `${GRAFANA_ADMIN_PASSWORD}`

## Notes
- Datasource provisioned через `grafana/provisioning/datasources/datasource.yml`.
- Dashboards provisioned через `grafana/provisioning/dashboards/dashboard-provider.yml` и файлы в `grafana/dashboards/`.
- В `docker-compose.events.yml` включён gzip (`GF_SERVER_ENABLE_GZIP=true`) для ускорения загрузки крупных `public/build/*.js`.

