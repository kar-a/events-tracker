## 1144: Подкатегории и дополнительные фильтры в приложении (ImShop feed param)

Files → `app/local/php_interface/lib/events/yandexmarket/onExportOfferWriteData.php`

Effects → В фид ImShop добавлен `param` с именем по `SUBCATEGORY_PROP_NAME` (например, «Подвид колец») и значением — название подвида (`UF_PLURAL`, `mb_ucfirst`). Дубли исключаются, значения экранируются.

Intent → Паритет с задачей 1099 (подвиды в фильтре/ЧПУ) и подготовка к реализации API фильтра ImShop.

---

### Context
- В 1099 реализовано разделение подвидов в фильтре и ЧПУ каталога.
- Для консистентности приложение должно получать подвид в фиде (YML → ImShop).
- `loadCategories()` уже формирует `SUBCATEGORY_PROP_NAME` для категорий с несколькими подвидамии.

### Key Decisions
- Добавлять `param` только если у категории задан `SUBCATEGORY_PROP_NAME` и такого параметра ещё нет у оффера (проверка через промежуточный `elementParams`).
- Имя параметра берётся из `SUBCATEGORY_PROP_NAME` (родительный падеж категории, конфиг через `Ctx::config()->catalog.iblock.{APP_IBLOCK_CATALOG}.categories.genitiveByName`).
- Значение параметра — `UF_PLURAL` подвида (`\SubcategoryTable`), приведённое к `mb_ucfirst` и экранированное через `htmlspecialchars`.
- Не изменять прочие `param` и не добавлять служебные атрибуты вида `noDetails` (раньше использовались и помечены как deprecated).

### Code Touchpoints
- `onExportOfferWriteData::getAdditionalProps()`
  - Сканирование существующих `param` и накопление `elementParams`.
  - Вставка `param` с именем из `SUBCATEGORY_PROP_NAME` при наличии `PROPERTY_SUBCATEGORY_VALUE`.
- `loadCategories()`
  - Расчёт `SUBCATEGORY_PROP_NAME` на основе дерева категорий и падежей (`morphos` + конфиг генитивов).
- `getSubcategory()/loadSubcategories()`
  - Доступ к `UF_PLURAL` для названия подвида.

### Gotchas
- Для категорий ровно с одним подвидом `SUBCATEGORY_PROP_NAME` пустой — `param` не добавляется (ожидаемое поведение).
- Если генератор фида ранее уже добавил одноимённый `param`, дубль не вносится (проверка `array_key_exists`).
- Корректность падежей зависит от конфига `genitiveByName`; при отсутствии используется `morphos`.

### Verification
- Сгенерировать YML и убедиться, что для категорий с >1 подвидом появляется `param` с именем «Подвид {род. падеж категории}» и значением подвида.
- Провалидировать отсутствие дублей `param` и корректную экранизацию HTML.
- Регресс: убедиться, что прочие `param` (цвет/видео/характеристики) не изменены.

### Follow-ups
- Использовать новый `param` в API фильтра ImShop для построения интерфейса выбора подвида в приложении.
- Добавить тест‑кейс в smoke‑проверки фида (категория с несколькими подвидами, без подвида, с единственным подвидом).


