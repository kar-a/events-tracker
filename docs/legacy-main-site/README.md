# Документация основного сайта **trimiata.ru** (Bitrix / PHP)

Эти материалы описывают **публичный интернет‑магазин** на ядре Bitrix и PHP. Они **не** описывают продукт **events.trimiata.ru** (Node, ClickHouse, рекомендации).

Используйте их, если вы ведёте смежный репозиторий с `app/` и `local/php_interface`, или нужен контекст для согласования событий с фронтом сайта.

## Оглавление (файлы в `docs/`)

| Документ | Тема |
|----------|------|
| [reference-architecture-main-site-bitrix.md](../reference-architecture-main-site-bitrix.md) | Поток запроса, каталог, API сайта |
| [project-overview.md](../project-overview.md) | В этом же файле ниже — краткий обзор **events**; далее — legacy Bitrix‑обзор |
| [onboarding-and-context.md](../onboarding-and-context.md) | Онбординг **events**; в конце — ссылка на Bitrix‑чеклист |
| [runbook.md](../runbook.md) | Сначала runbook **events**; затем — Bitrix dev/QA |
| [data-model.md](../data-model.md) | Сначала ClickHouse/контракт; ниже — IBLOCK/HL сайта |
| [integrations.md](../integrations.md) | Сначала продьюсеры → collector; ниже — ImShop, 1С и т.д. |
| [modules-and-components.md](../modules-and-components.md) | Компоненты Bitrix (к сайту) |
| [knowledge-map.md](../knowledge-map.md) | История задач в основном про сайт |
| [events-and-hooks.md](../events-and-hooks.md) | События D7 Bitrix (не путать с HTTP‑событиями аналитики) |
| [frontend-loaders.md](../frontend-loaders.md), [frontend-js-style.md](../frontend-js-style.md), [figma-and-build.md](../figma-and-build.md), [code-editing-rules.md](../code-editing-rules.md) | Фронт и сборка шаблона сайта |
| [security.md](../security.md) | Общая политика; для events см. раздел в начале файла |
| [security-and-quality.md](../security-and-quality.md), [security-audit-2026-01-27.md](../security-audit-2026-01-27.md) | Качество и аудит (в контексте сайта) |
| [git-and-github.md](../git-and-github.md) | Git — общий для команды |

## Память задач

Каталог [memory/](../memory/INDEX.md) в основном относится к **сайту** (каталог, заказы, ImShop). Для задач по **events-service** заводите записи рядом с кодом: `system/events-service/docs/` или отдельные карточки по договорённости команды.

