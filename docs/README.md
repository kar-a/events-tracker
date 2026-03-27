# Документация **events.trimiata.ru**

Репозиторий — **не** витрина интернет‑магазина **trimiata.ru** (там Bitrix, PHP, шаблон «Тримиата»). Здесь — **платформа для сбора событий, аналитики и построения системы рекомендаций**: от сырых событий до агрегатов и выдачи (по мере реализации).

## Цель продукта

1. **Накопление** поведенческих и контекстных данных (просмотры, поиск, корзина и т.д.) в **ClickHouse** в едином контракте.
2. **Анализ** и подготовка признаков (дашборды **Grafana**, запросы, джобы).
3. **Рекомендации** — отображение и обновление витрин (например, через **Redis** и сервисы на **Python**), без связки с ядром Bitrix в этом репозитории.

Публичный сайт и приложение выступают **продьюсерами**: они шлют события по HTTPS на хост **events.trimiata.ru** (коллектор).

## Стек (актуальный)

| Слой | Технологии |
|------|------------|
| Ingestion | Node.js (collector), HTTPS, контракт **JSON Schema / Zod** (`packages/contract`) |
| Хранилище событий | **ClickHouse** |
| Кэш / выдача (план) | **Redis** |
| Наблюдаемость | **Grafana** |
| Фоновая обработка | **Python** (каркас `apps/jobs-python`) |
| Инфраструктура | Docker Compose, Nginx (пример), скрипты ops |

Код: **`system/events-service/`** — см. **[STRUCTURE.md](../system/events-service/docs/STRUCTURE.md)**.

## С чего начать

| Документ | Зачем |
|----------|--------|
| [../README.md](../README.md) | Корень репозитория, быстрый старт `make up` |
| [architecture-map.md](./architecture-map.md) | Архитектура контура events, потоки данных |
| [build-and-ops.md](./build-and-ops.md) | Сборка, env, deploy; вначале — events, ниже справочно про Bitrix |
| [system/events-service/docs/architecture.md](../system/events-service/docs/architecture.md) | Детали стека |
| [system/events-service/docs/event-contract.md](../system/events-service/docs/event-contract.md) | Контракт события |
| [system/events-service/docs/deployment.md](../system/events-service/docs/deployment.md) | Деплой |
| [system/events-service/docs/debugging.md](../system/events-service/docs/debugging.md) | Отладка |
| [CHANGELOG.md](./CHANGELOG.md) | История изменений (в т.ч. смежные задачи сайта) |

## Безопасность и процессы

- [security.md](./security.md) — в начале файла: замечания по **контуру events**; основной текст ориентирован на веб‑сайт.
- [git-and-github.md](./git-and-github.md) — работа с Git.
- Политика **CHANGELOG / Definition of Done** — раздел [«Политика ведения Changelog»](#политика-ведения-changelog) ниже (общая для команды).

## Документация основного сайта (legacy)

Всё, что относится к **trimiata.ru**, **Bitrix**, каталогу и **PHP**, сведено в указатель: **[legacy-main-site/README.md](./legacy-main-site/README.md)**.

---

## Политика ведения Changelog

- Формат: Keep a Changelog + SemVer.
- После задачи — запись в [CHANGELOG.md](./CHANGELOG.md) (Unreleased): что сделано, эффект, пути к файлам.
- Для крупных тем: подпункты `Files → Effects → Intent`.
- При релизе — перенос Unreleased в версию с датой и тег.

### Commit summary для CI

По договорённости команды: краткое описание в `.github/commit-summary.txt` (блоки `Effects →`, `Intent →`) для уведомлений; подробности в [CHANGELOG.md](./CHANGELOG.md).

### Definition of Done (кратко)

- Обновлён `docs/CHANGELOG.md` при пользовательско‑заметных изменениях.
- Для **events-service**: при смене контракта или API — синхронно `packages/contract`, collector, документация контракта.

