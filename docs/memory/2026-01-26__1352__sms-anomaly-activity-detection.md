# Task Memory Card

Date: 2026-01-26
Task Key: 1352
Title: Обнаружение аномальной активности отправки СМС и алертинг

## Context
- Нужен контроль всплесков отправки СМС, чтобы быстро реагировать на потенциальные массовые/аномальные рассылки.
- Проверка должна работать автономно через cron и использовать уже принятый в проекте паттерн уведомлений через `Event::send`.
- Нужна простая и читаемая реализация без избыточной конфигурации.

## Key Decisions
- Вынести логику в отдельный сервис `App\Sms\Guard`.
- За окно `60` минут считать количество записей по дате добавления `UF_DATE_ADD`.
- Лимит сделать константой в коде (`LIMIT = 100`) без runtime-конфига.
- Метрику считать как сумму `UF_COUNT_ADD + 1` по каждой записи, чтобы учитывать повторы отправки при дедупликации строк очереди.
- При превышении лимита отправлять уведомление через `Event::send` (событие `SMS_GUARD_ALERT`).

## Code Touchpoints
- `app/local/php_interface/lib/app/Sms/Guard.php` → сервис проверки лимита и отправки уведомления.
- `app/local/cron/sms_anomaly_check.php` → cron entrypoint для запуска проверки и фиксации статуса выполнения.
- `app/local/php_interface/lib/app/Cron/RunCheck.php` → регистрация нового cron-кода `sms_anomaly_check` в мониторинге запусков.
- `app/local/php_interface/lib/app/inc.php` → автозагрузка класса `App\Sms\Guard`.
- `app/local/changes/db/1352/create_sms_anomaly_alert_event.php` → создание почтового события/шаблона для `Guard::EVENT_NAME`.

## Gotchas (Pitfalls)
- Для `Event::send` нужен настроенный почтовый тип/шаблон события `SMS_GUARD_ALERT`, иначе письма не уйдут.
- Проверка идёт по `UF_DATE_ADD`, а не по `UF_DATE_UPDATE`.
- Без cooldown уведомление будет уходить на каждый запуск cron, пока аномалия продолжается.

## Verification
- Запустить `php -l` для файлов `Guard.php`, `sms_anomaly_check.php`, `create_sms_anomaly_alert_event.php`.
- Выполнить cron вручную: `php app/local/cron/sms_anomaly_check.php`.
- Для теста аномалии временно снизить `Guard::LIMIT` (например до `1`) и убедиться, что создаётся событие `SMS_GUARD_ALERT`.

## Follow-ups
- При необходимости добавить cooldown или rate-limit уведомлений, если cron запускается часто.
- При необходимости расширить контроль: отдельные лимиты по источникам (`сайт`/`мобильное приложение`) и по номеру получателя.

