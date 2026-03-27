# Task Memory Card

Date: 2026-01-26
Task Key: 1354
Title: Telegram-бот уведомлений для SMS Guard

## Context
- В задаче 1352 был сделан тестовый mail-канал оповещений об аномальной СМС-активности.
- Требовалось перейти на Telegram-уведомления и вынести отправку в отдельный класс.

## Key Decisions
- Реализована структура `App\Telegram\Service` + `App\Telegram\Bot` по паттерну задачи 1140.
- `App\Sms\Guard` переключен на прямую отправку через `Bot::sendMessage()`, при этом текст уведомления формируется в `Guard`.
- Используются унифицированные константы `APP_TELEGRAM_INFO_BOT_TOKEN` и `APP_TELEGRAM_INFO_CHAT_ID`.
- `Guard::EVENT_NAME` сохранён для совместимости с mail fallback-скриптом 1352.

## Code Touchpoints
- `app/local/php_interface/lib/app/Telegram/{Service,Bot}.php` → транспорт и фасад Telegram Bot API.
- `app/local/php_interface/lib/app/Sms/Guard.php` → формирование сообщения и отправка через `Bot`.
- `app/local/php_interface/lib/app/inc.php` → автозагрузка Telegram-классов.
- `app/local/php_interface/inc/constants.php` → `APP_TELEGRAM_INFO_BOT_TOKEN`, `APP_TELEGRAM_INFO_CHAT_ID`.

## Gotchas (Pitfalls)
- Без заполненных `APP_TELEGRAM_INFO_BOT_TOKEN` и `APP_TELEGRAM_INFO_CHAT_ID` уведомления не отправятся.
- Бот должен быть добавлен в чат (или канал) с правом отправки сообщений.
- Для каналов обычно нужен chat_id вида `-100...`.

## Verification
- Проверить синтаксис: `php -l` для `Telegram/{Service,Bot}.php`, `Guard.php`, `inc.php`, `constants.php`.
- Выполнить cron `sms_anomaly_check` при заниженном лимите и убедиться, что сообщение пришло в Telegram.

## Follow-ups
- При необходимости добавить retry/логирование ошибок Telegram API (с ограничением по чувствительным данным).
- После стабилизации можно удалить тестовый mail fallback.

