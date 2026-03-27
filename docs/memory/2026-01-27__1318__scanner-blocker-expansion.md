# Task Memory Card
Date: 2026-01-27
Task Key: 1318
Title: Расширение списка адресов детекта сканнеров уязвимостей

## Context
- Механизм блокировки сканнеров уязвимостей работает через `checkScanners()` в `BeforeProlog.php` (событие `main:OnBeforeProlog`, приоритет 0).
- Проверяет три типа паттернов из `blocker.php`: `requestStarts` (начало URL), `requestIncludes` (наличие подстроки), `noReferer` (запрос без реферера).
- При совпадении блокирует IP через `CSecurityIPRule` на 30 дней, логирует в `_blockScanners_*`, возвращает 403.
- Текущие списки были неполными и содержали конфликтующие паттерны, блокировавшие легитимные URL.

## Key Decisions
- Расширить `requestStarts`: Bitrix-специфичные пути, конфиги, админ-панели, бэкапы БД, WordPress-паттерны.
- Расширить `requestIncludes`: shell-скрипты, варианты конфигов с расширениями бэкапов (.bak, .old, .orig, .save, .tmp, .backup, .swp, ~), паттерны для поиска секретов (.env, .ssh, id_rsa, server.key).
- Добавить `noReferer`: критичные пути без реферера (phpinfo.php, admin.php, wp-login.php, bitrix/admin, .env, backup.sql).
- Удалить из `requestIncludes` конфликтующие паттерны: '1/', '2/' и т.д. (блокировали детальные страницы с -1, пагинацию page-1), '/p/', '/c/', '/a/' и т.д. (блокировали /catalog/product-name/, /catalog/category/), 'catalog45', 'catalog10', 'catalog55' (конфликт с реальными URL).
- В `requestStarts` оставить корневые пути типа '/1/', '/2/' — они безопасны, так как проверяют начало URL.

## Code Touchpoints
- `app/local/php_interface/config/blocker.php`:
  - `requestStarts`: добавлены `/bitrix/restore.php`, `/bitrix/admin/bitrix.xscan`, `/bitrix/.settings.php`, `/bitrix/backup`, `/bitrix/cache`, `/bitrix/tmp`, `/phpinfo.php`, `/phpinfo`, `/info.php`, `/test.php`, `/.htaccess`, `/web.config`, `/wp-config.php` и варианты бэкапов, `/wp-json/`, `/wp-login.php`, `/administrator`, `/adminer.php`, `/phpmyadmin`, `/backup.sql`, `/backup.sql.gz`, `/dump.sql`, `/database.sql`, `/db.sql`.
  - `requestIncludes`: добавлены паттерны для shell-скриптов (xleet, c99, wso, shell, haxor, priv8), варианты конфигов с расширениями бэкапов, паттерны для поиска секретов (.env, .ssh, id_rsa, server.key, winscp.ini, FileZilla.xml), WordPress-паттерны (wp-json, wp-login, wp-admin, wp-includes), Bitrix-паттерны (bitrix.xscan, restore.php, virtual_routes.php), инструменты (adminer, phpmyadmin, pma), файлы с расширениями бэкапов (.bak, .old, .orig, .save, .tmp, .backup, .swp, ~). Удалены конфликтующие: '1/', '2/' и т.д., '/p/', '/c/', '/a/' и т.д., 'catalog45', 'catalog10', 'catalog55'.
  - `noReferer`: добавлены `/phpinfo.php`, `/phpinfo`, `/info.php`, `/test.php`, `/admin.php`, `/admin`, `/administrator`, `/wp-login.php`, `/wp-admin`, `/bitrix/admin`, `/bitrix/restore.php`, `/bitrix/.settings.php`, `/wp-config.php`, `/config.php`, `/.env`, `/backup.sql`, `/backup`.

## Gotchas (Pitfalls)
- `requestIncludes` проверяет наличие подстроки в URL, поэтому короткие паттерны типа '1/' или '/p/' слишком общие и блокируют легитимные URL.
- Реальные URL сайта: детальные страницы `/catalog/{category}/{product-name-{8digits}(-1)?}/` (могут заканчиваться на -1/), пагинация `/catalog/.../page-{N}/` (содержит page-1, page-2 и т.д.), категории `/catalog/{category}/` (могут содержать подстроки типа /p/, /c/).
- Паттерны в `requestStarts` безопаснее, так как проверяют начало URL, а не наличие подстроки.
- При добавлении новых паттернов нужно проверять конфликты с реальными URL через sitemap.xml и структуру URL каталога.

## Verification
- Проверка через sitemap.xml: URL типа `/catalog/product-name-12345678-1/` не блокируются паттернами '1/' (удалены из requestIncludes).
- URL типа `/catalog/category/page-1/` не блокируются паттернами '1/' (удалены из requestIncludes).
- URL типа `/catalog/product-name/` не блокируются паттерном '/p/' (удален из requestIncludes).
- URL типа `/catalog/category/` не блокируются паттерном '/c/' (удален из requestIncludes).
- Корневые пути типа `/1/`, `/2/` остаются в requestStarts и безопасно блокируются.

## Follow-ups
- Периодически проверять логи `_blockScanners_*` на ложные срабатывания.
- При обнаружении новых векторов атак добавлять паттерны в соответствующие секции, проверяя конфликты с реальными URL.
- Рассмотреть возможность добавления rate-limiting для более гибкой защиты от сканеров.
