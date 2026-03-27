## 1166 — JSON-LD ItemList для списков товаров

Context
- Требовалось добавить микроразметку `ItemList` на страницах списков товаров для улучшения понимания выдачи поисковиками.
- Опция включения: `$arParams['SET_SCHEMA_MARKUP'] == 'Y'`.

Key Decisions
- Генерировать JSON-LD только при не-AJAX запросе и наличии `arResult['ITEMS']`.
- Элементы: `@type: ListItem`, `position`, `url`, `name`.
- Вставка через `Asset::addString(..., AssetLocation::AFTER_JS)`.
 - Данные для ItemList готовятся в `result_modifier.php` и прокидываются в epilog через `arResult['ITEMS_LIST']` (epilog не делает расчёты).

Code Touchpoints
- `app/local/templates/trimiata/components/app/catalog.section/main/component_epilog.php` — добавлена сборка и вывод ItemList.

Verification
- На страницах категорий/подкатегорий с товарами в исходнике появляется `<script type="application/ld+json">{ "@type": "ItemList", ... }</script>`.
- Валидация в Rich Results Test проходит без ошибок.

Follow-ups
- При необходимости добавить `itemListOrder` и `itemListElement.item` c расширенными полями товара.

