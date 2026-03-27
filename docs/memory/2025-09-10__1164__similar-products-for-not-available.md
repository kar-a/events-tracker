# Task Memory Card

Date: 2025-09-10
Task Key: 1164
Title: Похожие предложения для недоступных товаров (детальная карточка)

## Context
- При `!CAN_BUY` на детальной отсутствовал быстрый путь к релевантным альтернативам, пользователь уходил в каталог вручную.

## Key Decisions
- Добавлена кнопка «Похожие …» в `component_epilog.php` карточки, видна только при `!$arResult['CAN_BUY']`.
- Ссылка строится через `Catalog\Helper::getSimilarProductsLink($product)`, который формирует SEF `/catalog/{CATEGORY}/{SUBCATEGORY}/` и добавляет параметры (BRAIDING) канонически через `getLinkByParams()`.
- Заголовок кнопки включает имя категории в нижнем регистре для более естественного текста (например, «Похожие браслеты»).

## Code Touchpoints
- Files → `app/local/templates/trimiata/components/app/catalog.element/main/component_epilog.php`
  - Кнопка «Похожие …» при `!CAN_BUY`.
- Files → `app/local/php_interface/lib/app/Catalog/Helper.php`
  - `getSimilarProductsLink($product)` — построение канонической ссылки (CATEGORY/SUBCATEGORY + BRAIDING).

## Gotchas
- Соблюдать URL‑политику: использовать только `getLinkByParams()` (канонический порядок/алиасы), не собирать ссылки вручную.
- Не показывать кнопку, если ссылку собрать не удалось (пустой return).
- Не перегружать критерии похожести без согласования — риск сузить выдачу.

## Verification
- Недоступный товар → кнопка отображается, ведёт в каталог с фильтрами.
- Доступный товар → кнопка не показывается.
- URL соответствует политике ЧПУ, фильтры применены корректно.

## Follow-ups
- Обсудить расширение критериев сходства (например, MATERIAL/FINENESS) и A/B позиционирование CTA.


