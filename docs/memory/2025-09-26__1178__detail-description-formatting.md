## 1178 — Форматирование детального описания в приложении

Context
- Приложение ImShop потребляет описание товара из экспортных событий (YML). HTML описаний с сайта приводил к нерегулярной разметке в клиенте.

Key Decisions
- Нормализовать `DETAIL_TEXT` в `description`: удалить HTML, сохранить абзацы/переносы по правилам (p→двойной перенос, br→одинарный).
- Добавить раскрываемый блок `collapsibleDescription` с секциями: «Характеристики», «Доставка», «Оплата», «Гарантии и возврат».
- Уточнить названия служб доставки в API (EMS/DPD) для консистентного отображения в приложении.

Code Touchpoints
- `app/local/php_interface/lib/events/yandexmarket/onExportOfferExtendData.php` — `formatDetailText()`, установка `description`.
- `app/local/php_interface/lib/events/yandexmarket/onExportOfferWriteData.php` — сборка `collapsibleDescription` (sections), сортировка `<param>`.
- `app/local/php_interface/lib/app/Order/External/ImShop.php` — корректные короткие названия доставок (EMS/DPD).

Gotchas
- Обрезка HTML должна сохранять читаемость (в т.ч. HTML entities, EOL унификация). Следить за двойными переносами (>2 → 2).
- Для imshop/2gis — исполнять обработчики только для нужных профилей (guard по имени профиля).

Verification
- Проверка выгрузки: убедиться, что `description` без HTML, параграфы корректны.
- В UI приложения: секции `collapsibleDescription` отображаются и складываются.

Follow-ups
- Вынести форматирование текста в отдельный helper при дальнейших доработках контента.

