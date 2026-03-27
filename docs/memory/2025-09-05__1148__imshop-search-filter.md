## 1148: Поддержка filter в webhook поиска ImShop

Context
- Задача: добавить поддержку фильтрации в webhook `app/api/webhook/search.php` без подъёма `smart.filter`.
- Источник фильтров: payload `appliedFilters` (список пар), диапазон цены передаётся набором значений `q_price`.

Key Decisions
- Отказ от поднятия `app:catalog.smart.filter`; вместо этого — пост‑фильтрация ID, полученных компонентом `app:search.page`.
- Свойства:
  - directory (HL) → перевод `name` в UF_XML_ID через `ImportHelper::getCode($name, true)` (без SQL).
  - LIST → маппинг VALUE → enum ID через `PropertyEnumerationTable` (кэш включён).
  - S/N → значения напрямую в `PROPERTY_<CODE>`.
- Цена: собирать диапазон как `min/max` из нескольких `q_price` и применять новым синтаксисом `>=PRICE_{APP_PRICE_ID}`/`<=PRICE_{APP_PRICE_ID}`.

Code Touchpoints
- `app/local/php_interface/lib/app/Order/External/ImShop.php::search()` — построение property‑filter и пост‑фильтрация через `CIBlockElement::GetList`.
- `.cursor/rules/bitrix.mdc` — обновлены правила: `PRICE_*` синтаксис, directory → UF_XML_ID через ImportHelper::getCode.
- `.github/commit-summary.txt` — commit summary для CI‑уведомлений.

Gotchas
- `appliedFilters` приходит как список объектов; один и тот же ключ может повторяться (для `q_price`).
- Следить за типом `$this->request` в базовом классе (`HttpRequest`), иначе линтер ругается на `isPost()`/`getInput()`.

Verification
- Запрос без фильтров возвращает ID как раньше.
- `appliedFilters: [{'f_Вставка':'Без вставок'}]` → `PROPERTY_INSERT='bez_vstavok'`.
- `appliedFilters: [{'q_price':42000},{'q_price':47082}]` → `>=PRICE_..=42000`, `<=PRICE_..=47082`.
- Смешанный набор свойств + цена сузит исходный список ID.

Follow-ups
- Привести базовый класс `External` к использованию `HttpRequest`.
- Вычистить неиспользуемый HL‑резолвер, если полностью отказались от SQL для directory.

