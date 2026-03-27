## Memory Card

Date: 2025-09-15
Task Key: 1170
Title: Разделение событий YML-выгрузки по профилям (imshop/2gis)

### Context
- Требуется отдельная выгрузка 2ГИС по адресу `/api/yml/2gis/`, аналогично существующей `imshop`.
- Нужно разделить обработчики событий модуля `yandex.market` по профилям выгрузки.
- Документация событий перенесена в `events/yandexmarket/documentation.md`.

### Key Decisions
- В обработчики добавлена фильтрация по имени профиля (`imshop` сейчас; `2gis` — для будущей логики) через параметры события (`SETUP/PROFILE/SETUP_ID`).
- Добавлен метод `YamarketExportSetupTable::getProfileById()` для резолвинга профиля по ID.
- В правила проекта добавлен раздел `export_yml_events` с требованиями по профилям и маршрутам.

### Code Touchpoints
- `app/local/php_interface/lib/events/yandexmarket/onExportOfferWriteData.php` — импорт `YamarketExportSetupTable`, добавлены `getSetupMeta()`/`isForProfiles()` и ранний выход, если профиль ≠ `imshop`.
- `app/local/php_interface/lib/events/yandexmarket/onExportOfferExtendData.php` — ранний выход, если профиль ≠ `imshop`.
- `app/local/php_interface/lib/app/Export/YamarketExportSetupTable.php` — метод `getProfileById()`.
- `events/yandexmarket/documentation.md` — перенесена дока по событиям YML.
- `.cursor/rules/bitrix.mdc` — добавлен раздел `export_yml_events` и контекст для YML.

### Gotchas
- Параметры события могут различаться по версиям модуля: используем безопасную выборку `SETUP/PROFILE` и ID.
- Нельзя ломать `imshop` — включили фильтрацию, изменений в бизнес-логике нет.

### Verification
- `/api/yml/imshop/` — без изменений, XML валиден.
- `/api/yml/2gis/` — отдаёт XML для профиля `2gis` (после добавления профиля в таблицу и генерации файла).
- Логи: отсутствуют ошибки в обработчиках при генерации разных профилей.

### Follow-ups
- Создать профиль `2gis` в `yamarket_export_setup`, настроить генерацию файла и наполнение по требованиям 2ГИС.
- Добавить специфичные для 2ГИС теги/правила, при необходимости — отдельные обработчики/ветви.

