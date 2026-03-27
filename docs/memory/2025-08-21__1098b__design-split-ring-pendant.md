## 1098b: Разделение DESIGN → DESIGN_RING/DESIGN_PENDANT; алиасы и импорт

Context
- Итерация 2 задачи 1098: разделить «Дизайн» для колец/подвесок; сохранить консистентность SEO/смарт‑фильтра.
- Свойства заранее созданы в ИБ: `DESIGN_RING`, `DESIGN_PENDANT`.

Key Decisions
- Алиасы свойств: только snake_case — `design_ring`→`dizayn_kolca`, `design_pendant`→`dizayn_podveski`.
- SEO‑regex: `DESIGN_RING` → `dizayn_kolca-([a-zA-Z0-9\-_or]+)`, `DESIGN_PENDANT` → `dizayn_podveski-([a-zA-Z0-9\-_or]+)`.
- Импорт: BRAIDING → `DESIGN_RING` для `koltsa`, → `DESIGN_PENDANT` для `podveski`; иначе — в `BRAIDING`.
- Стиль кода: предпочтителен `switch ($category['UF_XML_ID'] ?? '')` без временных переменных.

Code Touchpoints
- `app/local/php_interface/lib/app/Import/Base/Catalog.php` — маппинг BRAIDING через `switch`.
- `app/local/php_interface/lib/app/Seo/Seo.php` — добавлены новые regex‑префиксы.
- `app/local/php_interface/config/catalog.php` — добавлены алиасы в карту фильтра.
- `app/local/changes/db/1098/move_design_split.php` — перенос старых значений `DESIGN` в новые свойства и очистка.

Gotchas
- Не использовать дефисы в префиксах свойств: ломают парсинг префикса/канонический порядок; использовать подчёркивания.
- `SUBCATEGORY` не должен попадать в `SMART_FILTER_PATH`, если присутствует в базовом пути.

Verification
- Урлы вида `/catalog/dizayn_kolca_gvozd/` и `/catalog/dizayn_podveski_serdce/` применяют фильтр без редиректа на `/catalog/`.
- `Catalog::processElement` записывает ожидаемые свойства для `koltsa`/`podveski`.
- Документация обновлена: `docs/data-model.md`, `docs/knowledge-map.md`; changelog — актуален.

Follow-ups
- Мониторить SEO‑логи на неожиданные редиректы по фильтрам дизайна.
- Подумать о unit‑тестах для `Seo::getPropsRegulars()` и `Catalog\Helper::getLinkByParams()` (парсинг префикса).

