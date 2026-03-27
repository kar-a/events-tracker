Date: 2025-08-26
Task: 1096 — Минимальные и максимальные цены при применённых фильтрах

Context
- При применении фильтров (например `material-beloe_zoloto`, `proba-585`) диапазон цены оставался общим по каталогу (5050–710100), т.к. расчёт не учитывал активные фильтры.
- Фильтр: компонент `app:catalog.smart.filter`; шаблон подключается из `catalog.full/main/sections.php`.

Key Decisions
- Faceted: исправлен расчёт `FILTERED_VALUE` для MAX (использовать сравнение «<», чтобы брать максимум из среза).
- Non‑faceted: расчёт MIN/MAX выполнять по текущему фильтру элементов, исключив ценовые ключи (чтобы диапазон не был сам собой ограничен); MIN/MAX брать по `b_catalog_price` с `CATALOG_GROUP_ID = APP_PRICE_ID`.
- Шаблон цены использует `FILTERED_VALUE` (фолбэк `VALUE`) для отображения границ.

Code Touchpoints
- `app/local/components/app/catalog.smart.filter/component.php` — пересчёт MIN/MAX в non‑faceted ветке, импорт `Application`, SQL без управляющих последовательностей.
- `app/local/components/app/catalog.smart.filter/class.php` — faceted ветка: условие для MAX `FILTERED_VALUE`.
- `app/local/templates/trimiata/components/app/catalog.smart.filter/main/template.php` — вывод `FILTERED_VALUE`.

Gotchas
- Из фильтра перед расчётом диапазона необходимо удалить ценовые ключи (`CATALOG_PRICE_*`, `CATALOG_CURRENCY_SCALE_*`), иначе диапазон «сам себя» сужает.
- При faceted учитывать конвертацию валют (`convertCurrencyId`).

Verification
- URL A: `/catalog/material-beloe_zoloto/` → min/max меньше глобальных 5050/710100.
- URL B: `/catalog/material-beloe_zoloto/proba-585/` → min/max ещё меньше, чем в A.
- При добавлении/снятии любого свойства min/max обновляются.

Follow-ups
- При необходимости кэшировать агрегаты по ключу фильтра (hash от фильтра без ценовых ключей) с TTL, чтобы снизить нагрузку.

