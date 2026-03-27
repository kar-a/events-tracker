# Структура репозитория events-service

Каноническое устройство каталогов и ответственность. **Единственный источник DDL ClickHouse** — `infra/clickhouse/sql/`.

```
events-service/                          # корень продукта (в монорепо: system/events-service/)
├── Makefile                             # обёртка: up/down, контракт, init-ch, healthcheck, backup
├── docker-compose.yml                   # include → infra/compose/docker-compose.events.yml (Compose 2.20+)
├── .env.example                         # напоминание: рабочий шаблон — infra/compose/.env.example
├── README.md                            # быстрый старт и ссылки
│
├── apps/                                # всё, что собирается в образ и выполняется как процесс
│   ├── collector-node/                  # HTTP ingestion (Node/TS)
│   └── jobs-python/                     # фоновые джобы (каркас Python)
│
├── packages/                            # общие библиотеки без собственного deploy
│   └── contract/                        # схема события, Zod, тест-вектора (подкаталог node/ для npm-пакета)
│
├── infra/                               # инфраструктура как код (не бизнес-логика приложений)
│   ├── compose/
│   │   ├── docker-compose.events.yml   # сервисы: clickhouse, redis, collector, grafana, …
│   │   └── .env.example                 # единственный полный шаблон ENV для стека
│   ├── clickhouse/
│   │   ├── sql/                        # *.sql — DDL и seed-подобные скрипты
│   │   └── README.md                    # как монтируется и как прогонять init
│   ├── grafana/                         # дашборды, provisioning
│   ├── nginx/                           # пример vhost для events.trimiata.ru
│   └── docker/                          # справка по Dockerfile-путям в compose
│
├── scripts/                             # операции с хостом и стеком (bash)
│   ├── dev-up.sh                        # основной подъём
│   ├── init-clickhouse.sh
│   ├── 00_prepare_host.sh … 50_backup.sh
│   └── …
│
└── docs/                                # проектная документация продукта
    ├── STRUCTURE.md                     # этот файл
    ├── architecture.md
    ├── event-contract.md
    ├── deployment.md
    ├── debugging.md
    └── structure-comparison.md
```

## Предсказуемые правила

| Действие | Куда класть |
|----------|-------------|
| Новый HTTP-роут или middleware коллектора | `apps/collector-node/src/` (см. README приложения) |
| Изменить поля события | `packages/contract/schema/`, затем сборка `packages/contract/node`, consumer в collector |
| Новая таблица ClickHouse | `infra/clickhouse/sql/` + при необходимости обновить `scripts/init-clickhouse.sh` |
| Порты, пароли, тома | `infra/compose/docker-compose.events.yml` + `infra/compose/.env` |
| Дашборд / datasource Grafana | `infra/grafana/` |
| Одноразовые ops-команды | `scripts/` (номер префикса по порядку пайплайна: 00 host → 10 up → …) |

## Зависимости между слоями

```text
  producers (браузер, приложение, внешние джобы)
        ↓ HTTPS
  apps/collector-node  →  packages/contract  →  ClickHouse / Redis
        ↑
  apps/jobs-python     →  (агрегации, позже)
```

Не добавлять в этот репозиторий код основного сайта (Bitrix, `app/`).

