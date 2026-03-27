## 1099: Разделение подтипов для категорий товаров (множественный выбор подвидов)

Date: 2025-08-12

Context
- Требовалось поддержать множественный выбор подкатегорий (подвидов) для выбранных категорий и привести ЧПУ к единой схеме.
- В старом варианте подвиды попадали в умный путь (`SMART_FILTER_PATH`) как свойство с префиксом `podvid-*`, что порождало дубли и проблемы каноникал.

Key Decisions
- Базовый путь каталога формируется только из категорий/подкатегорий: `/catalog/{category}/{subA-i-subB}/`.
- Подкатегории (SUBCATEGORY) исключены из `SMART_FILTER_PATH` — они присутствуют в базовом пути, а не как свойство с префиксом.
- Для множественного выбора подвидов используется разделитель `-i-` внутри сегмента подвида.
- Канонический порядок фильтров обеспечивается `Catalog\Helper::checkUriOrder()`/`checkUriOrderAndRedirect()`.
- На клиенте: при изменении категорий/подкатегорий отправляются массивы `categories`/`subcategories`; для остальных свойств формируется `smartParts` через `encodeSmartParts()`.
- На сервере: экшены `categoriesFilter` и `subcategoryFilter` в `ajax_before.php` обрабатываются общий логикой — сбор `SEF_FOLDER` через `Catalog\Helper::getCategoriesUrl()`, затем добавление умного пути и 303‑редирект на `?action=smartFilterResult&ajaxId=...`.

Code Touchpoints
- `app/local/templates/trimiata/components/app/catalog.full/main/baseBlocks/ajax_before.php`
  - Добавлены экшены `categoriesFilter`/`subcategoryFilter` с установкой `SEF_FOLDER` и исключением SUBCATEGORY из `SMART_FILTER_PATH`.
  - Использование `checkUriOrder()` для канонического порядка.
- `app/local/components/app/catalog.smart.filter.categories/class.php`
  - Получение подкатегорий только для выбранных категорий.
  - Бизнес‑правило: если у выбранной категории ровно одна подкатегория — блок подкатегорий не показываем и не добавляем в `arResult['SUBCATEGORIES']`.
- `app/local/templates/trimiata/components/app/catalog.smart.filter.categories/main/template.php`
  - Разметка для множественного выбора подвидов.
- `app/local/templates/trimiata/js/AppSmartFilter.js`
  - Формирование массивов `categories`/`subcategories` на событии изменения.
  - Для прочих свойств — сбор `smartParts`; `category`/`subcategory` не попадают в `smartParts`.
- `.cursor/rules/bitrix.mdc`, `docs/architecture-map.md`
  - Закреплены правила URL/фильтра: субкатегории в базовом пути, без `podvid-*`, множественный выбор с `-i-`.

Gotchas
- Нельзя добавлять SUBCATEGORY в умный путь — приводит к дублям и некорректной каноникал/редиректам.
- Следить за `AJAX_ID` и перезапуском буфера в `ajax_before.php` перед редиректами.
- На клиенте исключить `category`/`subcategory` из списка элементов, формирующих `smartParts`.
- Поддерживать трейлинг‑слэш и регистр URL согласно нормализации в `BeforeProlog`.

Verification
- Пример: `/catalog/cepi/kardanskii-i-yakornyi/` открывается без редиректа на `/catalog/` и применяет обе подкатегории.
- Снятие подвида пересобирает базовый путь и корректно исключает его из умного пути.
- `checkUriOrderAndRedirect()` не меняет порядок сегментов; 303 ведёт на `?action=smartFilterResult&ajaxId=...` и загружает JSON‑блоки.

Follow-ups
- Добавить автотесты на кодирование/декодирование `-i-` для SUBCATEGORY и проверку отсутствия префикса `podvid-*` в `SMART_FILTER_PATH`.
- Обновлять документацию при добавлении новых уровней в иерархии каталога.


