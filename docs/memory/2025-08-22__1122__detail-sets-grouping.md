# Task Memory Card
Date: 2025-08-22
Task Key: 1122
Title: Group duplicate items in "Complete the set" on product detail

## Context
- В блоке «Дополните комплект» могли дублироваться товары одного типа.
- В каталоге предусмотрен компонент `app:catalog.section.groups`, группирующий товары (используется в списках при APP_USE_CATALOG_GROUPS).

## Key Decisions
- В `component_epilog` детальной переключать компонент комплектов на `app:catalog.section.groups`, когда `APP_USE_CATALOG_GROUPS === true`.
- Для группирующего компонента добавить параметры пагинации: `PAGEN_ID='n1'`, `PAGEN_SEF_MODE='N'` (обязательные для инициализации `PageNavigation`).

## Code Touchpoints
- `app/local/templates/trimiata/components/app/catalog.element/main/component_epilog.php` — выбор `$componentName` и добавление `PAGEN_ID`/`PAGEN_SEF_MODE`.

## Verification
- Страница детальной загружается без ошибок; блок комплектов не содержит дублей при включенной опции группировки.

## Follow-ups
- По необходимости отобразить пейджер (`DISPLAY_BOTTOM_PAGER='Y'`) для длинных списков комплектов.


