# Task Memory Card
Date: 2025-08-25
Task Key: 1140
Title: Telegram↔Notion Task Bot (Service pattern; API core; cron)

## Context
- Требовалось сделать бота: пересланные сообщения из Telegram создают задачи в Notion; бот отвечает ссылкой, а при переходе статуса в Testing/Done присылает уведомления.
- Принят стандарт внешних интеграций: `Service` (HTTP, __call) + фасад (доменные методы), хранение данных интеграций в отдельных папках `upload/<service>/`.

## Key Decisions
- Вебхук перенесён в `app/api/webhook/telegram/task-bot.php` и использует `api/core.php` + `ApiResult`.
- Интеграции:
  - Notion: `App\Notion\Service` + `App\Notion\Notion`.
  - Telegram: `App\Telegram\Service` + `App\Telegram\Bot`.
- Крон: `app/local/cron/notion/status_sync.php` — тонкая обвязка без curl, уведомляет по Testing/Done.
- Карта соответствий хранится в `upload/notion/telegram_map.json` (раздельные директории для интеграций).

## Code Touchpoints
- `app/api/webhook/telegram/task-bot.php` — вебхук, создание задачи, ответ в чат, запись mapping.
- `app/local/cron/notion/status_sync.php` — опрос статуса и уведомления.
- `app/local/php_interface/lib/app/Notion/{Service,Notion}.php` — Notion API.
- `app/local/php_interface/lib/app/Telegram/{Service,Bot}.php` — Telegram API.
- `app/local/php_interface/lib/app/inc.php` — автозагрузка классов.

## Gotchas
- Вебхук должен использовать `api/core.php` для унификации JSON/статусов и отключения лишних проверок.
- Секреты — только из `.env` через `constants.php`.
- Раздельные папки `upload/<service>/` не смешивать с общими файлами.

## Verification
- Пересылка сообщения → создаётся страница в Notion, в чате приходит ссылка.
- Перевод статуса в Notion в Testing/Done → приходит уведомление в исходный чат.

## Follow-ups
- При необходимости расширить маппера полей Notion (labels, assignee, deadlines).
- Вынести хранение mapping в KV (Memcache/Redis) при росте нагрузки.


