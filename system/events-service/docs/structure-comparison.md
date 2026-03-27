# Сравнение: предложенная структура vs текущая

## Предложение другой модели (кратко)

Плоская схема: `collector-node/`, `python-pipeline/` в корне, `docker-compose.yml`, `Makefile`, `sql/clickhouse/*.sql`, `infra/docker/*.Dockerfile`, разнесённые `routes/`, `middleware/`, `events/`, `queue/`.

## Плюсы предложения

| Аспект | Зачем это хорошо |
|--------|------------------|
| **Корневой compose + Makefile** | Одна команда `make up` / `docker compose up` из корня репозитория — меньше когнитивной нагрузки. |
| **Документация по слоям** | `deployment.md`, `debugging.md`, `event-contract.md` — эксплуатация не завязана только на README. |
| **Node: routes + middleware + events/** | Явное разделение транспорта (HTTP), сквозной обработки (auth, request-id) и домена (валидация, DLQ) — проще масштабировать код. |
| **Python: cli / storage / metrika / recommendations** | Доменные пакеты по зонам ответственности — удобно, когда появится реальный код джобов. |
| **Нумерованные SQL** | `001_`, `010_` — понятный порядок миграций. |
| **Nginx в infra** | Шаблон vhost для `events.trimiata.ru` рядом с сервисом — меньше «магии» на сервере. |

## Минусы и риски

| Риск | Почему |
|------|--------|
| **Дублирование контракта** | Если `events/types.ts` живёт только в collector, Python и Node разъедутся. У нас контракт вынесен в `packages/contract` — это сознательно лучше. |
| **Плоский `collector-node/` в корне** | При росте монорепы (ещё сервисы) корень захламляется; префикс `apps/` держит приложения отдельно от `infra/` и `packages/`. |
| **Глубокие деревья до появления кода** | Пустые `middleware/`, `queue/` усложняют навигацию без пользы до первых PR. |
| **Два места для Dockerfiles** | `infra/docker/collector.Dockerfile` vs `apps/collector-node/Dockerfile` — дублирование build context; пока один Dockerfile рядом с приложением проще. |
| **include в compose** | Требует Docker Compose с поддержкой `include` (v2.20+); иначе остаётся канонический путь `infra/compose/`. |

## Текущая схема (что сохраняем)

- **`packages/contract`** — единая точка правды (Zod + JSON Schema + vectors).
- **`apps/collector-node`**, **`apps/jobs-python`** — явное разделение приложений.
- **`infra/compose`**, **`infra/clickhouse/sql`**, **`infra/grafana`** — инфраструктура отдельно от кода.

## Что мы добавили из предложения (без поломки монорепы)

- Корневой **`Makefile`** и опциональный **`docker-compose.yml`** (include) для запуска из корня.
- Расширенные **`docs/`**: контракт, деплой, отладка, **`STRUCTURE.md`**, этот сравнительный документ.
- **`infra/clickhouse/README.md`** — где лежат миграции (`infra/clickhouse/sql/`).
- **`infra/nginx/`** — пример vhost.
- **`infra/docker/README.md`** — где лежат Dockerfile и почему.
- Заготовка дашборда **`user-journey.json`** (минимальная).

Эволюция Node-кода в сторону `routes/` + `middleware/` — отдельный рефакторинг, когда появятся auth, request-id и DLQ.
