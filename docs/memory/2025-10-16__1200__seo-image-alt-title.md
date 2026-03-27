## 1200: SEO ALT & TITLE изображений в списках товаров

Context
- Цель: улучшить SEO и CTR изображений в каталоге. Требуется генерировать человеко‑понятные ALT/TITLE на списках (в т.ч. при APP_USE_CATALOG_GROUPS).
- Источник данных: `NAME`, `PROPERTIES.FINENESS` (проба/материал), `PROPERTIES.INSERT` (вставки), артикул из `MAIN_ITEM.EXTERNAL_ID` → `ITEMS[].EXTERNAL_ID` → `XML_ID`.

Key Decisions
- Генерация вынесена в `App\Catalog\Helper::getSeoImageAltTitle(array $product)` без лишних переменных.
- Склонение материала выполняется через `morphos` (`NounDeclension::getCase(..., Cases::RODIT)`), без собственных словарей.
- Для групп (`catalog.section.groups`) компонент собирает `PROPERTIES` (FINENESS/INSERT) и формирует `DISPLAY_VALUE` для FINENESS из HL `FinenessTable`.
- Исключаем повторы: если признак уже есть в `NAME`, не добавляем его в ALT/TITLE.
- TITLE: «… | Ювелирный дом «Тримиата»», ALT включает «— Арт. …» при наличии.

Code Touchpoints
- `app/local/php_interface/lib/app/Catalog/Helper.php` — `getSeoImageAltTitle()`: PROPERTIES→DISPLAY_PROPERTIES→HL fallback; morphos; группировка артикулов.
- `app/local/components/app/catalog.section.groups/class.php` — сбор `PROPERTIES` (FINENESS/INSERT/MATERIAL) для групп, DISPLAY_VALUE для FINENESS через HL.
- `app/local/templates/trimiata/components/app/catalog.section/main/result_modifier.php` — использование ALT/TITLE из хелпера для `IMAGE` и `IMAGES_LIST`.

Gotchas
- На листинге не всегда есть `DISPLAY_PROPERTIES` — используем `PROPERTIES` и фолбэки.
- Вставки часто уже в `NAME` («с бриллиантом») — не дублировать.
- Для FINENESS возможны массивы значений — берём первое значимое.

Verification
- `/catalog/`, `/catalog/koltsa/`: ALT примерно «Кольцо с бриллиантом — из золота 585 пробы — Арт. 00002622», TITLE «… | Ювелирный дом «Тримиата»».
- При отсутствии FINENESS/INSERT: ALT=NAME, TITLE=NAME | бренд.
- Проверка группированных товаров (catalog.section.groups): ALT/TITLE присутствуют и консистентны.

Follow-ups
- Расширить список свойств (при необходимости): DESIGN_RING/DESIGN_PENDANT, цвет золота.
- Применить аналогичную логику для детальной страницы (если нужно унифицировать ALT/TITLE галереи).

