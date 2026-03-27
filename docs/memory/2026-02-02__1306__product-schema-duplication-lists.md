# Task Memory Card
Date: 2026-02-02
Task Key: 1306
Title: Дублирование schema @type: Product в списках товаров

## Context
- На страницах списков товаров (каталог: категория, подкатегория, модель) выводились и разметка типа Product (с AggregateOffer), и ItemList, что создавало дублирование и некорректную семантику для поисковиков.
- По schema.org на списке товаров допустим только ItemList; Product уместен только на странице одного товара.
- Логика подстановки #SCHEMA_NAME#/#SCHEMA_DESCRIPTION# и вывода JSON-LD/og:meta находилась в `Seo::replaceAdditionalParams()` и могла приводить к повторной вставке при нескольких вызовах.

## Key Decisions
- На списках товаров выводить только ItemList (убрать Product с AggregateOffer).
- Формирование и вывод всех JSON-LD и og:meta (title, description, og:type) перенести в один обработчик — `OnBeforeEndBufferContent::addSeoAdditionalParams()` — чтобы добавление в буфер происходило один раз при завершении страницы.
- Для ItemList передавать в Ctx::share() уже готовую JSON-строку с подставленными name/description; подстановку плейсхолдеров делать в OnBeforeEndBufferContent перед выводом.
- Для изображения в ItemList использовать PICTURES из первого товара списка; если списка нет — fallback на logo.svg. PICTURES задавать в result_modifier и прокидывать в arResult (кэш-ключи), чтобы component_epilog мог построить абсолютный URL изображения для schema.
- На детальной странице в Product оставить только одну разметку Product; добавить абсолютные url и image; при необходимости скорректировать aggregateRating.

## Code Touchpoints
- `app/local/php_interface/lib/app/Seo/Seo.php`: удалена логика вывода og:title, og:description и JSON-LD (section/element schema) из `replaceAdditionalParams()`.
- `app/local/php_interface/lib/events/main/OnBeforeEndBufferContent.php`: добавлена логика добавления og:title, og:description, подстановки #SCHEMA_NAME#/#SCHEMA_DESCRIPTION# в section/element schema и вывода одного скрипта ld+json для section и одного для element, плюс og:type (product.group / product).
- `app/local/templates/trimiata/components/app/catalog.section/main/component_epilog.php`: убрана сборка и вывод Product-схемы с AggregateOffer; при SET_SCHEMA_MARKUP формируется только ItemList (url, name, description, image, numberOfItems, itemListOrder, itemListElement), image — абсолютный URL из arResult['PICTURES']; результат передаётся в Ctx::share()->set('catalog.section.schema', $schema). Удалён отладочный вызов pre($arParams['PICTURES']).
- `app/local/templates/trimiata/components/app/catalog.section/main/result_modifier.php`: если PICTURES не заданы, заполняются из первого элемента списка или fallback logo.svg; PICTURES прокидываются в $cp->arResult и в SetResultCacheKeys.
- `app/local/templates/trimiata/components/app/catalog.element/main/component_epilog.php`: в Product-схему добавлены url (абсолютный), абсолютный URL для image; скорректированы aggregateRating (ratingValue 4–5, worstRating 4, reviewCount 3–50).

## Verification
- На странице списка товаров в исходном коде один блок `<script type="application/ld+json">` с @type: ItemList, без Product.
- На детальной странице товара один блок с @type: Product с корректными url и image.
- og:title, og:description, og:type и ld+json не дублируются при обновлении страницы.

## Follow-ups
- При необходимости проверить другие страницы (поиск, коллекции) на наличие лишней Product-разметки и единообразие с OnBeforeEndBufferContent.
