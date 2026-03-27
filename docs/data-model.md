## Модель данных — **events.trimiata.ru**

### ClickHouse и контракт
- События попадают в сырые таблицы (например `events_raw`) после приёма collector’ом; DDL — `system/events-service/infra/clickhouse/sql/`.
- Смысл полей и версия payload — [system/events-service/docs/event-contract.md](../system/events-service/docs/event-contract.md) и `system/events-service/packages/contract/` (JSON Schema, общие типы).
- Таблицы для джоб и рекомендаций описываются в той же папке SQL по мере развития продукта (см. [architecture-map.md](./architecture-map.md)).

### Кэш и выдача (план)
- **Redis** — в compose; конкретные ключи/схемы задаются задачами на рекомендации, не в этом файле.

Данные **основного сайта** (инфоблоки, HL каталога) описаны ниже — для согласования артикулов, категорий и событий с витриной.

---

## Модель данных — каталог **trimiata.ru** (Bitrix)

### Инфоблоки (IBLOCK)
- `APP_IBLOCK_CATALOG = 50` — каталог товаров (NIМ/товар).
- `APP_IBLOCK_CATALOG_SKU = 51` — торговые предложения (OFFERS).
- Прочие контентные ИБ: новости, баннеры, сервисы — см. `app/local/php_interface/inc/constants.php`.

Ключевые свойства элементов каталога (используются в коде):
- `CATEGORY` — код товарной группы (`UF_XML_ID` из HL Category).
- `SUBCATEGORY` — код подгруппы (`UF_XML_ID` из HL Subcategory).
- `MODEL` — код модели (`UF_XML_ID` из HL Model).
- `SET` — комплект (для группировки, влияет на конструктор).
- `IMAGES`, `VIDEO` — JSON со списками файлов в Media Store.
- `DESIGN` — дизайн изделия (общий). Для категорий `koltsa`,`podveski` ранее использовался; теперь для них применяются отдельные свойства.
- `DESIGN_RING` — дизайн кольца. Для категории `koltsa` заполняется из «Плетение» (BRAIDING) при импорте.
- `DESIGN_PENDANT` — дизайн подвески. Для категории `podveski` заполняется из «Плетение» (BRAIDING) при импорте.

OFFERS‑свойства (набор вариантов в конструкторе), см. `App\Catalog\Helper::OFFERS_PROPS`:
- `COLOR`, `DIAMETER`, `SIZE`, `LUG`, `PROCESSING`, `FINENESS`.
  - Для значений используются справочные HL‑таблицы, например `\ColorTable`, `\DiameterTable`, `\FinenessTable`.

### Highload‑блоки (иерархия каталога)
- Category (`\CategoryTable`):
  - Поля (по использованию в коде): `UF_XML_ID` (код), `UF_NAME`, `UF_EXTERNAL_CODE`, `UF_ICON`, `UF_IMAGE`, `UF_IMAGES?`, `UF_ACTIVE`.
  - URL: `/catalog/{UF_XML_ID}/`.
- Subcategory (`\SubcategoryTable`):
  - Поля: `UF_XML_ID` (код), `UF_CATEGORY` (FK → Category.ID), `UF_PLURAL` (наименование во мн. числе), `UF_EXTERNAL_CODE`, `UF_IMAGES?`, `UF_ACTIVE`.
  - URL: `/catalog/{category.UF_XML_ID}/{UF_XML_ID}/`.
- Model (`\ModelTable`):
  - Поля: `UF_XML_ID` (код), `UF_CATEGORY`, `UF_SUBCATEGORY`, `UF_NAME`, `UF_EXTERNAL_CODE`, `UF_IMAGES?`, `UF_ACTIVE`.
  - URL: `/catalog/{category}/{subcategory}/{UF_XML_ID}/`.

Запросы к HL (паттерны):
- Всегда фильтр по `=UF_ACTIVE => true` и выборка по коду `=UF_XML_ID`. Индексы на `UF_XML_ID`, `UF_ACTIVE` крайне желательны.

### Формирование дерева категорий
- Источник: `App\Catalog\Helper::getCategoriesTree($getData=true, $clearTree=true)`.
  - Собирает: Категории → Подкатегории → Модели; добавляет `URL`, `SELECTED`, `PICTURES`.
  - Использует данные из `app:catalog.full` (в т.ч. текущий маршрут) для выделения выбранных элементов.
  - Очищает «дубли» и формирует редиректы через `clearCategoriesTree()` (напр., перенос модели в подкатегории если совпадают коды).

### URL‑политика каталога
- Категория: `/catalog/{category}/`
- Подкатегория: `/catalog/{category}/{subcategory}/`
- Модель: `/catalog/{category}/{subcategory}/{model}/`
- Множественный выбор сегмента — разделитель `-i-` (напр., несколько категорий или подкатегорий).
- Канонический порядок фильтра: `Catalog\Helper::checkUriOrder()`/`checkUriOrderAndRedirect()`.

### Построение ссылок фильтра
- Разбор текущего адреса: `Catalog\Helper::getCurrentFilterLinkParams()`.
- Сборка адреса по параметрам: `Catalog\Helper::getLinkByParams($sefFolder, $linkParams)`.
  - Карта префиксов свойств берётся из `Seo::getPropsRegulars()`.
  - Алиасы значений — из конфига: `Ctx::config()->get('catalog.iblock.' . APP_IBLOCK_CATALOG . '.filter.aliases')`.
  - Для диапазонов используются `ot-`/`do-`, комбинированные значения — `combine:`.
  - Алиасы свойств: `design` → `dizayn`, `design_ring` → `dizayn_kolca`, `design_pendant` → `dizayn_podveski` (см. `config/catalog.php`).

### Кеширование и константы
- TTL: `APP_CACHE_M/H/D/W/MN/Y` (см. `constants.php`).
- Часто используемые справочники (цвета, пробы, размеры) кешируются на уровне D7 (`cache_joins => true`) и через `App\Cache\Helper`.

### Витрина/данные для фронта
- Картинки/видео: `Catalog\Helper::getProductImages()`/`getProductVideo()` — строят URLы в Media Store (`APP_MEDIA_STORE_HOST`).
- Минимальная цена/вес: `getProductMinPrice()`/`getProductMinWeight()`; персональные цены с учётом карт — `modifyProductPrice()`.
- DataLayer категория: `getProductDataLayerCategory()` — собирает Breadcrumb из HL‑таблиц.

### Системные конфигурации (Ctx::config())
- Конфиги: `app/local/php_interface/config/*.php`. Чтение: `Ctx::config()->get('path.to.key')`.
- Каталог: `config/catalog.php`.
  - `catalog.categories.genitiveByName` — карта склонений названий категорий (родительный), используется для заголовков подвидов в `catalog.smart.filter.categories`.
  - `catalog.iblock.{APP_IBLOCK_CATALOG}.filter.aliases` — префиксы фильтра и алиасы значений.
  - `catalog.googleProductTaxonomy` — таксономия Google для фидов.

### Проверочные тезисы (быстро проверить при изменениях)
- Для всех ссылок убедиться, что `checkUriOrderAndRedirect()` не меняет порядок (иначе 301).
- Для сабсегмента SUBCATEGORY — используется чистый сегмент в базовом пути; префиксы не применяются.
- Для множественных значений в сегменте — разделитель `-i-`; для свойств — `-or-`.
