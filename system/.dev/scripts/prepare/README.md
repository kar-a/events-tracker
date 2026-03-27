# Подготовка хоста (legacy-путь)

Операционные скрипты и compose для контура событий перенесены в **`system/events-service/`**.

**Используйте только:**

- `system/events-service/Makefile` — `make up`, `make init-ch`, `make prepare-host`, `make healthcheck`, и т.д.
- `system/events-service/scripts/*.sh` — те же шаги без `make`
- `system/events-service/infra/compose/.env` — скопируйте из `infra/compose/.env.example`

Документация: `system/events-service/README.md`, `system/events-service/docs/deployment.md`.

Задача **1359** (исторический контекст): `app/local/changes/db/1359/README.md`.
