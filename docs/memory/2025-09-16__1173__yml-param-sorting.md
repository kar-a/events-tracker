## Memory Card

Date: 2025-09-16
Task Key: 1173
Title: Сортировка `<param>` в YML по конфигурации

### Context
- Требуется управляемый порядок тегов `<param>` в `<offer>` результирующих YML‑фидов.
- Конфигурация порядка передана в `app/local/php_interface/config/catalog.php` → ключ `catalog.iblock.{APP_IBLOCK_CATALOG}.filter.sort`.
- Задача выполнена в рамках 1171.

### Key Decisions
- Реализована сортировка в обработчике событий записи XML (WriteData), чтобы гарантировать итоговый порядок.
- Чтение карты сортировки через `Ctx::config()->get('catalog.iblock.' . APP_IBLOCK_CATALOG . '.filter.sort')`.
- Невключённые в карту теги получают приоритет `PHP_INT_MAX` и остаются после отсортированных, с сохранением исходного относительного порядка (stable sort по индексу).

### Code Touchpoints
- `app/local/php_interface/lib/events/yandexmarket/onExportOfferWriteData.php` — метод `sortParamsByConfig(\SimpleXMLElement $tagNode)` и вызов перед `invalidateXmlContents()`.
- `app/local/php_interface/config/catalog.php` — секция `filter.sort` с примерами (например, «Тип товара», «Подвид колец», «Размер», «Дизайн кольца»).

### Gotchas
- Изменения делаются в WriteData (не ExtendData), иначе порядок может быть перезаписан при формировании узлов.
- Имена в `filter.sort` должны совпадать с атрибутом `name` у `<param>` в XML.

### Verification
- Сгенерировать YML и проверить, что `<param>` идут в порядке, заданном в `filter.sort`, остальные — после них.
- Добавление/удаление ключей в `filter.sort` меняет порядок без правки кода.

### Follow-ups
- При необходимости добавить сортировку специфичных секций (например, группировка по семействам параметров), оставляя эту реализацию как базовую.

