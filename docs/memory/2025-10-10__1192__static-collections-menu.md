Задача: 1192 — Статические подборки из дизайн‑макетов

Контекст и цель
- Добавить в меню каталога статические подборки согласно макетам Figma: «Вид золота», «Вставки», а также блок specials («Бестселлеры», «Новинки»).
- Обеспечить единый источник правды через конфиг и минимальные правки в компонентах/шаблонах.

Изменения (Files → Effects)
- app/local/php_interface/config/catalog.php
  - Добавлены `catalog.iblock.{APP_IBLOCK_CATALOG}.menu.staticCollections` (группы goldTypes/inserts) и `catalog.iblock.{...}.menu.specials` (ITEMS с NAME/URL/ICON и RESTRICTION_FILTER).
- app/local/components/app/catalog.section.list/class.php
  - Подключён `Ctx`; метод `getStaticCollections()` читает конфиг `menu.staticCollections` и формирует ссылки `CatalogHelper::getLinkByParams` по `PROPERTY/ VALUE` (без алиасов, согласно требованиям).
- app/local/templates/trimiata/components/app/catalog.section.list/header/template.php
  - Универсальный вывод статических подборок: любой набор групп из `STATIC_COLLECTIONS` + вывод `SPECIALS` после списка категорий в `menu__list_right__ul`.
- Ассеты: `app/local/changes/template/src/img/menu/{gold-types,inserts,specials}/*@2x.*` и их bundle‑копии (lazyload, retina).
- app/local/components/app/catalog.full/class.php
  - Роутинг specials: URL из конфига открываются как `COMPONENT_PAGE=collection`; построение унифицированного `arResult['COLLECTION']` (CODE, NAME, ITEMS, FILTER). Для коллекций 1С `FILTER` строится по `=XML_ID`.

Правила/инварианты
- Не использовать алиасы для links в меню (следовать конфигу PROPERTY/ VALUE).
- Страницы specials обрабатываются как коллекции в `prepareDataCollection()`; единая структура COLLECTION.
- Иконки — retina (`*@2x.*`), lazyload; пути — `SITE_TEMPLATE_PATH/bundle/...` (нужна сборка фронта).

QA/Checks
- Меню: видны разделы «Вид золота», «Вставки», карточки кликабельны, иконки загружаются из bundle.
- /catalog/bestsellery/ и /catalog/new/: корректные H1/Title, применённый фильтр из RESTRICTION_FILTER.
- Кэш шаблонов/компонентов очищен после деплоя.

Итог
- Меню каталога соответствует макету; маркетинговые подборки доступны по чистым URL и обрабатываются единообразно с коллекциями из 1С.

