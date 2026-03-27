# 1359: events.trimiata.ru - контур сбора событий и витрина данных

Цель: поднять отдельную площадку `events.trimiata.ru` для сбора и хранения событий рекомендаций.

В рамках этого набора:
- инфраструктурный старт (Docker + ClickHouse + Redis);
- SQL-структура для сырых событий и джобов импорта;
- базовые операционные скрипты;
- пример вставки тестового события.

Реализация API-коллектора (`POST /v1/events`) в этот шаг не входит.

---

## Структура

- `docker-compose.events.yml` - контейнеры `clickhouse`, `redis`, `collector` (заглушка)
- `.env.example` - переменные окружения
- `sql/01_clickhouse_events_raw.sql` - таблица событий
- `sql/02_clickhouse_import_jobs.sql` - таблица статусов импортов
- `sql/03_clickhouse_recommendation_pairs.sql` - пример таблицы пар рекомендаций
- `scripts/00_prepare_host.sh` - подготовка хоста
- `scripts/10_up.sh` - запуск контура
- `scripts/20_init_clickhouse.sh` - создание таблиц
- `scripts/30_healthcheck.sh` - проверки доступности
- `scripts/40_demo_insert.sh` - вставка тестового события
- `scripts/50_backup.sh` - бэкап ClickHouse

---

## 1) Подготовка сервера

```bash
cd /path/to/repo/app/local/changes/db/1359
cp .env.example .env
bash /path/to/repo/app/local/changes/db/1359/scripts/00_prepare_host.sh
```

После этого:
- установлены `docker`, `docker compose plugin`, `curl`, `jq`;
- создана структура данных в `EVENTS_DATA_ROOT` (абсолютный путь из `.env`), например:
  - `/var/www/www-root/data/www/dev/bitrix-dev/system/events-service/data/clickhouse/data`
  - `/var/www/www-root/data/www/dev/bitrix-dev/system/events-service/data/clickhouse/log`
  - `/var/www/www-root/data/www/dev/bitrix-dev/system/events-service/data/redis/data`
  - `/var/www/www-root/data/www/dev/bitrix-dev/system/events-service/data/backups`

---

## 2) Запуск окружения

```bash
cd /path/to/repo/app/local/changes/db/1359
cp .env.example .env
docker compose -f docker-compose.events.yml --env-file .env up -d
```

Если `docker compose` недоступен (старый Docker), используйте:

```bash
docker-compose -f docker-compose.events.yml --env-file .env up -d
```

Или shortcut:

```bash
bash /path/to/repo/app/local/changes/db/1359/scripts/10_up.sh
```

---

## 3) Инициализация ClickHouse

```bash
bash /path/to/repo/app/local/changes/db/1359/scripts/20_init_clickhouse.sh
```

Скрипт применит SQL из папки `sql/`:
- `events_raw`
- `import_jobs`
- `recommendation_pairs`

---

## 4) Health-check

```bash
bash /path/to/repo/app/local/changes/db/1359/scripts/30_healthcheck.sh
```

Проверяется:
- HTTP ping ClickHouse (`/ping`);
- SQL ping ClickHouse (`SELECT 1`);
- PING Redis.

---

## 5) Тестовая запись события

```bash
bash /path/to/repo/app/local/changes/db/1359/scripts/40_demo_insert.sh
```

После вставки можно проверить:

```bash
docker exec trimiata-events-clickhouse clickhouse-client \
  --query "SELECT event_type, product_id, user_key, event_time FROM events_raw ORDER BY ingested_at DESC LIMIT 5"
```

---

## 6) Резервное копирование

```bash
bash /path/to/repo/app/local/changes/db/1359/scripts/50_backup.sh
```

Скрипт пишет `.sql.gz` в `${EVENTS_DATA_ROOT}/backups`.

---

## Минимальный rollout-план для events.trimiata.ru

1. Поднять сервер (или VM) под контур событий.
2. Развернуть этот комплект (`docker-compose + sql + scripts`).
3. Настроить DNS `events.trimiata.ru` -> IP сервера.
4. Поставить reverse-proxy (Nginx/Caddy) с TLS.
5. Добавить API-коллектор (следующий шаг задачи 1356).
6. Включить отправку событий с сайта в новый endpoint.
7. Настроить cron:
   - импорт исторических событий (Metrika Logs API);
   - агрегации и запись top-N в Redis.

---

## Команды эксплуатации

```bash
# Логи сервисов
docker compose -f docker-compose.events.yml logs -f clickhouse
docker compose -f docker-compose.events.yml logs -f collector

# Перезапуск
docker compose -f docker-compose.events.yml restart

# Остановка
docker compose -f docker-compose.events.yml down

# Проверка таблиц
docker exec trimiata-events-clickhouse clickhouse-client --query "SHOW TABLES FROM trimiata_events"
```

---

## Важно

- Не хранить секреты в репозитории, только в `.env` на сервере.
- Порты ClickHouse и Redis наружу не публиковать в production без необходимости.
- `collector` в compose - заглушка, чтобы зафиксировать контракт инфраструктуры заранее.
