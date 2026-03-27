# Task Memory Card
Date: 2025-08-22 
Task Key: 1098
Title: Map BRAIDING → DESIGN for rings/pendants; SEO alias `design`→`dizayn`

## Context
- У категорий «Кольца» и «Подвески» свойство «Плетение» должно трактоваться как «Дизайн».
- Импорт из 1С заполняет свойства НИМа; нужно переназначить выгрузку и очистить исторические данные.

## Key Decisions
- Импорт (`App\Import\Base\Catalog::processElement`): если `UF_XML_ID` категории ∈ [`koltsa`,`podveski`], писать значение плетения в `DESIGN` и не заполнять `BRAIDING`.
- DB‑скрипт `app/local/changes/db/1098/script.php`: очистить `BRAIDING` у товаров указанных категорий.
- SEO/фильтр: добавлен `DESIGN` в `Seo::$urlParamCodes` (`dizayn-*`), `propUrlTags`, `additionalParamCodes`.
- Алиас фильтра: `design` → `dizayn` в `config/catalog.php`.
 
## Code Touchpoints
- `app/local/php_interface/lib/app/Import/Base/Catalog.php` — условие по `UF_XML_ID` категории (`koltsa`,`podveski`), запись только в `DESIGN`.
- `app/local/changes/db/1098/script.php` — очистка значений `BRAIDING`.
- `app/local/php_interface/lib/app/Seo/Seo.php` — карта параметров/тегов (DESIGN).
- `app/local/php_interface/config/catalog.php` — алиас `design` → `dizayn`.

## Gotchas
- Проверить фасетный индекс после очистки `BRAIDING` (перестроение).
- Убедиться, что шаблоны/фильтр не ожидают `BRAIDING` для колец/подвесок.

## Verification
- Импорт товара «Кольца/Подвески»: `DESIGN` заполнен, `BRAIDING` пуст.
- Смарт‑фильтр по адресам `.../dizayn-.../` работает, теги и SEO‑шаблоны подставляются.
- Скрипт очистки сообщает количество обновлённых элементов.

## Follow-ups
- Проверить, нет ли использования `BRAIDING` в UI для колец/подвесок; заменить на `DESIGN`.


