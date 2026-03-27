## 1161 — Прогрессивный слайдер цены (гистограмма + values)

Context
- Пользовательские «От/До» (например, 1164, 21001) не отражались на слайдере: бегунки «прилипали» к ближайшим узлам.
- Нужно динамически строить шкалу по гистограмме цен текущего среза и поддержать точные значения из URL/GET.

Key Decisions
- Построение гистограммы и шкалы вынесено в `AppCatalogSmartFilter::buildPriceHistogram()`.
- Константы вместо магических чисел: `PRICES_HISTOGRAM_{BINS,CAP,EXTRA_TICKS_FACTOR,ROUND_STEP}`.
- Кастомные «от/до» берём из `getCHECK()` (по CONTROL_NAME цены), добавляем в values без округления; «липкие» при усечении (cap).
- Шаблон передаёт индексы (`data-indexes`) при наличии values.
- JS вставляет произвольные значения в `PRICE_VALUES` (ensureValueInValues) и обновляет бегунки (`ionRangeSlider({values,from,to})`).

Code Touchpoints
- `app/local/components/app/catalog.smart.filter/class.php` — метод `buildPriceHistogram`, константы PRICES_*, вызов из `setPriceValues()`.
- `app/local/templates/trimiata/components/app/catalog.smart.filter/main/template.php` — `data-indexes` + индексы в `data-from/to`.
- `app/local/templates/trimiata/js/AppSmartFilter.js` — вставка произвольных значений, обновление слайдера в режиме values.

Gotchas
- Не округлять пользовательские значения «от/до»; округление только для авто‑узлов шкалы.
- Клэмпить кастомные значения в пределах фактического min/max.
- Согласовать URL↔UI: проверять, что бегунки стоят ровно на значениях из URL.

Verification
- `/catalog/.../tsena-ot-1164-do-218350/` — values содержит 1164; бегунок «От» на 1164.
- `/catalog/.../tsena-ot-21001-do-218350/` — values содержит 21001; бегунок «От» на 21001.
- Изменение полей «От/До» вставляет значения в `PRICE_VALUES` и синхронизирует бегунки.

Follow-ups
- Почистить ленты: `convertEncoding(...,4)`, phpdoc `convertUrlToCheck()`/`getCHECK()`, старые обращения к `Ctx::config()` по массивному API в других местах.

