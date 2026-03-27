# 1263: Отправка SMS уведомлений для сертификатов (тестовая версия)

**Date**: 2026-01-05  
**Status**: Completed (test version)

## Context
Необходимо реализовать отправку SMS уведомлений для подарочных сертификатов аналогично существующей системе SMS для заказов. Если в сертификате указан сотовый номер получателя или отправителя, система должна автоматически отправлять SMS при отправке сертификата по email. Существующая система SMS для заказов реализована в `OnBeforeEventSend::OrderSmsNotification()` и использует события `SALE_SMS_*` и `KOPTELNYA_SMS_*`.

## Key Decisions
- Использовать существующую систему отправки SMS через `Sms::send()` → переиспользование инфраструктуры → единообразие с заказами
- Добавить обработчик `CertificateSmsNotification` в событие `OnBeforeEventSend` → автоматическая отправка при отправке email → синхронизация с email-уведомлениями
- Телефоны получателя и отправителя сохранять в свойства корзины заказа → доступность данных в событиях → возможность использования в шаблонах
- Создать отдельные события `GIFT_CERTIFICATE_RECIPIENT_SMS` и `GIFT_CERTIFICATE_SENDER_SMS` → разделение логики для получателя и отправителя → возможность разных шаблонов
- Поля телефонов необязательные в форме → не нарушает существующий UX → постепенное внедрение
- Реализовать тестовую версию → проверка работоспособности перед полным внедрением → минимизация рисков

## Code Touchpoints
- `app/local/php_interface/lib/events/main/OnBeforeEventSend.php`
  - Добавлен обработчик `CertificateSmsNotification()` в событие `OnBeforeEventSend`
  - Метод проверяет события `GIFT_CERTIFICATE_RECIPIENT_MAIL` и `GIFT_CERTIFICATE_SENDER_MAIL`
  - Извлекает телефоны из полей события (`RECIPIENT_PHONE`)
  - Нормализует телефоны через `Phone::normalize()`
  - Ищет шаблоны SMS событий (`GIFT_CERTIFICATE_RECIPIENT_SMS` и `GIFT_CERTIFICATE_SENDER_SMS`)
  - Заменяет макросы в шаблонах и отправляет SMS через `Sms::send()`
  - Логирует отправку в `_sms_mess_certificate` и ошибки в `_certificate_sms_errors`
- `app/local/php_interface/lib/app/Catalog/Certificate.php`
  - Добавлены поля `RECIPIENT_PHONE` в метод `getFormFields()`
  - Поля необязательные, тип `tel`, с placeholder для телефона
- `app/local/php_interface/lib/app/Order/External/Certificate.php`
  - В методе `buildCertificateProps()` добавлено сохранение `RECIPIENT_PHONE` (SORT: 410) в свойства корзины
  - Телефоны сохраняются вместе с другими данными сертификата
- `app/local/components/app/catalog.certificate.form/class.php`
  - В методе `submitForm()` добавлено получение `RECIPIENT_PHONE` из запроса
- `app/local/php_interface/lib/app/Order/Helper.php`
  - В методе `prepareCertificateData()` добавлена передача `RECIPIENT_PHONE` в поля события
  - Телефоны доступны в шаблонах email и SMS событий
- `app/local/changes/db/1263/create_certificate_sms_events.php`
  - Скрипт для автоматического создания событий и шаблонов SMS
  - Создает события `GIFT_CERTIFICATE_RECIPIENT_SMS` и `GIFT_CERTIFICATE_SENDER_SMS`
  - Создает шаблоны сообщений с текстом по умолчанию
  - Идемпотентен: проверяет существование перед созданием

## Gotchas (Pitfalls)
- Телефоны должны быть валидными для отправки SMS → используется `Phone::normalize()` и проверка через `Phone::isCorrect()` в обработчике → некорректные телефоны не отправляются
- События SMS должны быть созданы до использования → необходимо запустить скрипт `create_certificate_sms_events.php` → без событий SMS не отправляются
- Телефоны передаются в события через `prepareCertificateData()` → необходимо убедиться, что данные сертификата содержат телефоны → проверка в `getCertificateDataFromOrder()`
- Обработчик работает только для событий `GIFT_CERTIFICATE_RECIPIENT_MAIL` и `GIFT_CERTIFICATE_SENDER_MAIL` → при изменении имен событий необходимо обновить обработчик
- SMS отправляются синхронно при отправке email → может замедлить отправку сертификата → рекомендуется мониторить время выполнения
- Логирование в `_sms_mess_certificate` и `_certificate_sms_errors` → необходимо настроить ротацию логов → избежать переполнения диска

## Verification
- Запустить скрипт `create_certificate_sms_events.php` и проверить создание событий в админке Bitrix
- Проверить наличие полей телефонов в форме сертификата
- Создать тестовый заказ сертификата с телефоном получателя и проверить отправку SMS
- Создать тестовый заказ сертификата с телефоном отправителя и проверить отправку SMS
- Проверить логи в `upload/logs/_sms_mess_certificate/` и `upload/logs/_certificate_sms_errors/`
- Убедиться, что SMS не отправляются при отсутствии телефонов
- Проверить корректность замены макросов в шаблонах SMS

## Follow-ups
- Протестировать отправку SMS на реальных номерах
- Настроить шаблоны SMS с корректным текстом и короткими ссылками
- Добавить мониторинг успешности отправки SMS
- Рассмотреть возможность асинхронной отправки SMS для улучшения производительности
- Добавить настройку включения/выключения SMS уведомлений для сертификатов
- После тестирования перевести в production версию
