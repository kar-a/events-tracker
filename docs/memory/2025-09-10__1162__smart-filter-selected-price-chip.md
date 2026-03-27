# Task Memory Card

Date: 2025-09-10
Task Key: 1162
Title: Быстрые фильтры — чип цены через APPLIED_ITEMS

## Context
- В быстрых фильтрах отсутствовал чип для цены; ранее цена не попадала в `APPLIED_ITEMS` и обрабатывалась отдельно в компоненте `catalog.smart.filter.selected`.
- Требование: выводить ценовой диапазон как чип, работать по общей схеме построения, без частных кейсов.

## Key Decisions
- Формировать `APPLIED_ITEMS['PRICE']` в `result_modifier.php` смарт‑фильтра на основе `MIN/MAX.HTML_VALUE`.
- `URL_ID` формировать как `ot-<min>-do-<max>` (или `ot-…`/`do-…`), чтобы общая логика чипов умела удалять это значение.
- Подпись чипа: «от N ₽», «до N ₽» или «N – M ₽» (валюта в символе; эн‑даш для диапазона в HTML — `&ndash;`).
- Удалить частную обработку цены из `catalog.smart.filter.selected/class.php` — использовать общую ветку `APPLIED_ITEMS`.

## Code Touchpoints
- Files → `app/local/templates/trimiata/components/app/catalog.smart.filter/main/result_modifier.php`
  - Добавлено наполнение `APPLIED_ITEMS['PRICE']`.
- Files → `app/local/components/app/catalog.smart.filter.selected/class.php`
  - Удалена частная ветка формирования чипа цены; остаётся общая обработка `APPLIED_ITEMS`.

## Gotchas
- Следить за формированием `URL_ID`: он должен соответствовать тому, что парсит общая логика (`URL_ID` → удаление из `$_params[$code]`).
- Если будут локализации валюты — вынести символ/формат в helper/конфиг.

## Verification
- Кейс «от»: появляется чип «от N ¤» (валюта в HTML‑символе), удаление снимает только цену.
- Кейс «до»: появляется чип «до N ¤», удаление снимает только цену.
- Кейс «диапазон»: «N – M ¤», удаление снимает только цену.
- Совместно с остальными чипами — порядок сортировки сохранён.

## Follow-ups
- Вынести формат валюты в единый helper, если потребуется иная валюта/локаль.


