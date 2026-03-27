# events.trimiata.ru — репозиторий контура событий

Проект предназначен **только** для площадки **events.trimiata.ru**: приём событий, хранение (ClickHouse), вспомогательные сервисы (Redis, Grafana), общий **контракт** данных.

## Что здесь есть

| Путь | Назначение |
|------|------------|
| **`system/events-service/`** | Основной код: collector (Node), контракт, infra, compose, Grafana, скрипты. **Точка входа:** [system/events-service/README.md](system/events-service/README.md) |
| **`docs/`** | Документация репозитория: архитектура по продукту events, changelog, справка по смежному сайту Bitrix |
| **`system/.dev/`** | Служебные скрипты разработки (например, устаревший указатель `scripts/prepare`) |

## Чего здесь нет (намеренно)

- **Нет DocumentRoot Bitrix и каталога `app/`** как части этого продукта. Публичный сайт **trimiata.ru**, PHP, компоненты, `local/php_interface` живут в **другом репозитории/клоне**.
- Чтобы не путать контекст при согласовании событий, пути и потоки основного сайта вынесены в справочник: [docs/reference-architecture-main-site-bitrix.md](docs/reference-architecture-main-site-bitrix.md).

## Быстрый старт

```bash
cd system/events-service
cp infra/compose/.env.example infra/compose/.env
make up
```

Подробнее: [system/events-service/README.md](system/events-service/README.md).

## Документация

- **Каноническая структура кода:** [system/events-service/docs/STRUCTURE.md](system/events-service/docs/STRUCTURE.md)
- Архитектура продукта events: [docs/architecture-map.md](docs/architecture-map.md)
- Журнал изменений: [docs/CHANGELOG.md](docs/CHANGELOG.md)
- Полный индекс доков: [docs/README.md](docs/README.md)
