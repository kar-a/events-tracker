## Knowledge Map (deep): Files → Effects → Intent

Legend
- Формат: HASH | DATE | SUBJECT → Изменённые зоны → Эффект на сайт → Зачем (гипотеза)
- Карта сгруппирована по темам. Детальные пути указываем проектно‑относительно.

### Каталог и смарт‑фильтр

- 8f19f2aa9 | 2025-08-12 | 1099: Разделение подтипов для категорий товаров (Работа ИИ)
  - Areas: `app/local/components/app/catalog.full/class.php`, `.../catalog.full/main/baseBlocks/ajax_before.php`, `.../catalog.smart.filter.categories/*`, `app/local/changes/template/src/js/app/AppSmartFilter.js`
  - Effect: добавлен множественный выбор подвидов (`-i-`), ЧПУ `/catalog/{cat}/{subA-i-subB}/`, исключение SUBCATEGORY из SMART_FILTER_PATH.
  - Why: улучшение UX и SEO‑чистоты URL, паритет поведения категорий и подвидов.

- 7ea16fad2 | 2025-07-22 | 1085: Каталог V2: Фикс (и серия PR по 1085)
  - Areas: `components/app/catalog.*`, `templates/trimiata/components/app/catalog.*`, `app/local/changes/template/src/js/app/AppSmartFilter.js`
  - Effect: ускорение фильтрации (AJAX), порядок параметров, правки верстки/шаблонов.
  - Why: модернизация UX каталога, мобильные улучшения.

- pending | 2025-09-10 | 1161: Прогрессивный слайдер цены (гистограмма + values)
  - Areas: `app/local/components/app/catalog.smart.filter/class.php` (`buildPriceHistogram`, константы `PRICES_HISTOGRAM_*`), `templates/.../catalog.smart.filter/main/template.php` (индексы `data-indexes`), `app/local/changes/template/src/js/app/AppSmartFilter.js` (вставка произвольных значений в `PRICE_VALUES`).
  - Effect: точное соответствие бегунков произвольным значениям «от/до» из URL/GET; более плавная шкала в «густых» диапазонах цен; отсутствие «прилипания» к ближайшим узлам.
  - Why: улучшить UX фильтра по цене и согласовать URL↔UI.

- 2025-09-18 | 1175: Быстрая покупка (Compact detail в aside/pane, thumbnails srcset)
  - Areas: `app/api/modal/product-quick-buy.php`, `app/local/changes/template/src/js/{App.js,app/AppBasket.js}`, `templates/.../catalog.element/main/{result_modifier.php,template.php,component_epilog.php}`
  - Effect: по клику `data-role=product-show-offers` открывается сайдбар/мобильная панель с компактной карточкой; в `COMPACT_MODE` скрыты тяжёлые блоки, основной слайдер; миниатюры используют `IMAGES_LIST` (`SRCSET/SIZES`) по паттерну 1133; слайдер/zoom работают внутри `CupertinoPane` через `$.initialize`.
  - Why: повысить конверсию за счёт быстрого выбора веса/варианта без ухода со списка; единый responsive‑подход к медиа.

### Лояльность / QR / JWT

- d3557c132 | 2025-07-23 | 1119: QR код на сайте и в приложении: JWT токен
  - Areas: `api/modal/loyalty-qr.php`, `App\Auth\JWT`, шаблоны ЛК
  - Effect: выдача QR, привязка к пользователю; добавлен JWT хелпер.
  - Why: интеграция с программой лояльности, кросс‑канальный UX.

### Описание товаров в приложении / IMSHOP
 - 2025-09-16 | 1149.b: Вебхук фильтров ImShop — полный набор фильтров, подвиды по категориям, сортировка
  - Areas: `app/api/webhook/{index.php,filter.php}`, `App/Order/External/ImShop`, `config/catalog.php`
  - Effect: возвращаются «Тип изделия», отдельные блоки «подвид <род.п. категории>» (только при >1 подвида), а также свойства из smart.filter (range/checkbox); порядок блоков соответствует `catalog.iblock.{APP_IBLOCK_CATALOG}.filter.sort`
  - Why: паритет моб. приложения с сайтом, предсказуемые контракты API, отсутствие дублей/шума в UI

- 9ff780603 | 2025-07-23 | 1123: Описания товаров в мобильном приложении
  - Areas: `App/Order/External/ImShop`, `api/webhook/*`
  - Effect: прокидывание описаний/фидов в приложение.
  - Why: улучшение карточки товара в приложении.

### Навигация/поиск/мобильные фиксы

- 2355624e8 | 2025-07-23 | 1126: Фикс открытия поиска на мобильном
  - Areas: `app/local/changes/template/src/js/app/AppHeader*.js`, шаблоны хедера
  - Effect: исправлено открытие поиска.
  - Why: улучшение мобильного UX.

---

### Практические шпаргалки

- Категорные URL
  - Категории: `/catalog/{catA-i-catB}/`
  - Подвиды: `/catalog/{cat}/{subA-i-subB}/`
  - Модели: `/catalog/{cat}/{sub}/{model}/`

- Где править поведение URL
  - `app/local/components/app/catalog.full/class.php::getUrlTemplates()` и `prepareData*()`
  - `.../catalog.full/main/baseBlocks/ajax_before.php` для AJAX редиректов

- Где расширять фильтры
  - `app/local/components/app/catalog.smart.filter*`
  - `app/local/changes/template/src/js/app/AppSmartFilter.js`
  - Заголовки подвидов строятся с учётом конфигурации `catalog.categories.genitiveByName` (через `Ctx::config()`), см. `config/catalog.php` и шаблон `catalog.smart.filter.categories`.

- Изображения в шаблонах каталога (cheatsheet)
  - Данные для шаблона готовим в `result_modifier.php`.
  - Списки: `IMAGES_LIST`/`IMAGE` на элементе, Детали/быстрая покупка: `IMAGES_LIST` на `arResult`.
  - Ключи: `SRC`, `SRCSET`, `SIZES`, `ALT`, `TITLE`.
  - `SRCSET` собираем по `Template\Helper::IMAGE_SIZES`; `SIZES` — `TemplateHelper::getListImageSizes()`.
  - Разметка: `<img class="lazyload" data-src=... data-srcset=... data-sizes=... alt=... title=...>`.

- Подвески/Кольца — терминология плетения/дизайна
  - Для категорий с кодами `koltsa`,`podveski` свойство «Плетение» трактуется как «Дизайн».
  - Импорт: `Catalog::processElement` записывает только `DESIGN` (BRAIDING не используется).
  - SEO/фильтр: путь `dizayn-*`, алиас `design` → `dizayn` в `config/catalog.php`.
  - Разделение дизайна (1098b):
    - Свойства: `DESIGN_RING` (`dizayn_kolca-*`), `DESIGN_PENDANT` (`dizayn_podveski-*`).
    - Импорт: записывает BRAIDING → `DESIGN_RING`/`DESIGN_PENDANT` по категории; общий `DESIGN` очищён миграцией.

### Внешние интеграции (паттерн Service)
- Архитектура:
  - Класс `Service` инкапсулирует HTTP: заголовки, хосты, curl, `__call($method, $args)`.
  - Исполняемый класс-обёртка (фасад) маршрутизирует доменные методы в `Service` и собирает полезные пейлоады.
  - Примеры: `Exchange/TrimiataTg/Service.php` + `TrimiataTg.php`, `Yandex/Service.php` + `Webmaster.php`.
  - Новые интеграции:
    - Notion: `App\Notion\Service` + `App\Notion\Notion`.
    - Telegram: `App\Telegram\Service` + `App\Telegram\Bot`.
- Правила:
  - Вебхуки/API подключают `app/api/core.php` и работают через `ApiResult`.
  - Крон‑скрипты — тонкая обвязка: грузят фасад, вызывают методы, без inline curl.
  - Секреты берём из `.env` → `constants.php` (см. `APP_TG_TASK_BOT_TOKEN`, `APP_NOTION_API_KEY`, `APP_NOTION_DATABASE_ID`).
  - Хранилище upload для интеграций — раздельные папки по сервисам: `upload/notion/*`, `upload/telegram/*` и т.п., чтобы не смешивать данные.
  - Алиасы свойств: snake_case с подчёркиванием (напр., `dizayn_kolca`), чтобы префиксы корректно парсились и совпадали с трансформацией значений в ссылках.

### Figma: импорт токенов и PoC генерации верстки
- Импорт токенов/миксинов: `app/local/cron/figma.php`, классы `App\Figma\*` (цвета, шрифты, mixins `_color/_style`).
- [PoC] Генерация верстки по node_id:
  - Pull (REST→DSL): `system/figma/pull/index.js` — DevMode API, строит JSON DSL (layout/text/fill).
  - Codegen (DSL→HTML+SCSS): `system/figma/codegen/index.js` — BEM, data-role, partials, demo/apply.
  - Оркестрация: `system/figma/run/index.js` — `--nodes=<id>[,<id>]`, `--mode=demo|apply`.
  - NPM: `app/local/changes/template/package.json` → `figma:node`, `figma:pull`, `figma:pull+build`, `figma:apply`.
  - ENV: `FIGMA_PROJECT_FILE_KEY`, `FIGMA_PERSONAL_ACCESS_TOKEN`.

### Памятка по кэшу
- При добавлении данных в `arResult` компонентов: проверять, что не ломаем ключи кеша.
- Правила инвалидации фиксировать в PR и в этом файле.

## Knowledge Map: Tasks, Commits, File Impacts, Intent (Trimiata Bitrix project)

Legend
- Task IDs come from commit subjects (e.g., 1085, 1079). Merges indicate integration points.
- Impacts describe likely user-facing/site effects inferred from changed files.
- Code areas use backticks for quick lookup.

### Index of Epics/Themes
- Catalog V2 and Smart Filter (1085, 1078, 1079, 1087, 1118, 1089)
- ImShop (mobile) integration and feed (705, 742, 747, 756, 744, 777, 973, 940, 864, 863, 817, 848, 1105, 1123, 1119)
- Payments and post-payment UX (923, 721, 916, 1020, 614, 680)
- Delivery stack (706, 787, 1060, 1065, 567)
- SEO/Meta/Sitemap/Robots (511, 989, 1031, 1117, 1120)
- Wishlist/Loyalty (1002, 1119, 968)
- Sessions/Infra/DevOps (1016 and infra series)

---

### Catalog V2 and Smart Filter
- Goals: modernize catalog UX (menu, filters), apply filters without reload, improve mobile menu & headers, expose product description.
- Key commits
  - 1078 (Filter v2), 1079 (Apply without reload, parts 1–6), 1085 (Catalog V2), 1087 (Product description load/display), 1118 (sorting fix), 1089 (import notify fix).
  - 1076, 1077: preceding UI/UX fixes; 1077 “Buy” button on product detail.
- Changed Areas and Impacts
  - Components `app/local/components/app/*`:
    - `catalog.full`, `catalog.section`, `catalog.section.list`, `catalog.search.full`, `catalog.smart.filter*` → logic for section rendering and filter pipelines.
  - Template overrides `app/local/templates/trimiata/components/app/*` and Bitrix templates (menu, pagination) → UI/markup/states.
  - Frontend JS `app/local/changes/template/src/js/app/*`: `AppSmartFilter.js`, `AppHeaderMenu.js`, `App.js`, `AppCategoriesFilter.js` → client-side filter state, header/menu behaviors (исходники, компилируются через webpack в `app/local/templates/trimiata/dist/js/`).
  - Styles `app/local/changes/template/src/styles/...` and bundles → visual layout, mobile panes, badges, category cards.
  - Helpers `app/local/php_interface/lib/app/Catalog/Helper.php`, `Template/Helper.php` → server-side glue (computed properties, H1/init, codes), likely used by templates.
  - Resulting site changes: faster filtering (AJAX), new mobile catalog menu, clearer filter chips, open description tab, refined breadcrumbs/title behavior, consistent styling.
- Intent/Why
  - Reduce navigation friction on mobile, increase product discovery, improve perceived performance, align SEO (H1/breadcrumbs) with new layouts.

### ImShop integration and feed
- Goals: 2-way integration with mobile app (orders, payments, delivery methods, user data), feed improvements (YML), deep links, analytics.
- Key commits and threads
  - Statuses and source: 705 (send statuses), 742 (store order source; filter sending by source), 747 (payment restrictions per source), 756 (installId checks).
  - Basket properties and deeplinks: 744 (save basket element props), 777 (deeplinks to delivery/shops/guarantee from product details).
  - Feed/YML: 940 (sliders in feed), 817 (market category; extra attributes like noDetails/noFilter), 864/863 (feed restructuring), 973 (remove noDetails in compare feed).
  - Discounts/Descriptions/QR: 847 (discounts), 1105 (sets/IMSHOP data), 1123 (product descriptions in app), 1119 (loyalty QR on site/app).
  - Mail on card payments: 848 (send order email when paid by card).
- Changed Areas and Impacts
  - Webhooks `app/api/webhook/*`: `get-delivery-methods.php`, `get-pay-systems.php`, `calculate-basket.php`, `create-order.php`, `process-payment.php`, `orders-history.php`, `order-set-status.php`, `loyalty.php` → app-facing API.
  - Domain `app/local/php_interface/lib/app/Order/External/ImShop.php` → end-to-end order init, delivery/payment calculation, status syncing, personal prices.
  - Exchange stack `Exchange::*` usage → queueing to 1C; `Order\Helper` maps (statuses, deliveries, payments, platforms).
  - Export `app/local/php_interface/lib/app/Export/Yml.php` + events `events/yandexmarket/*` → feed categories, sliders, attributes for app; сортировка `<param>` по `catalog.iblock.{APP_IBLOCK_CATALOG}.filter.sort` (WriteData).
  - Templates/JS additions for deeplinks and promo/discounts.
  - Resulting site/app changes: consistent app order flow, accurate delivery options and prices, payment URL generation, status syncing, feed enriched with sliders/categories; QR-based loyalty interactions.
- Intent/Why
  - Feature parity and smooth UX between site and app; reliable order/payment/delivery integrations; marketing/personalization via discounts and loyalty.

### Payments and post-payment UX
- Goals: support T‑installments, stretch-banner for unpaid orders with switchable payment system, mobile post-payment flow, deferred payments, reliable payment webhooks.
- Key commits: 923 (T‑installments codes/handlers), 721 (stretch UI + change pay system), 916 (mobile post-payment improvements), 1020 (deferred payment v2), 614/680 (payment info/webhook fixes).
- Changed Areas and Impacts
  - `Order\Helper`: `PAY_SYSTEM_*`, mappings to app/1C, promo codes for Tinkoff; helper methods for online/offline checks.
  - `ImShop` payment flow: `processPayment` creation/capture; handling different PS peculiarities (Tinkoff promo, OTP flags).
  - Templates (header/footer) and JS for payment retry (`AppOrderPaymentRetry.js`), UI states.
  - Resulting site changes: user can re-pay, switch payment methods, mobile-friendly flows; payments propagate correctly to app and 1C.
- Intent/Why
  - Improve payment conversion, support BNPL/credit products, reduce failed checkouts.

### Delivery stack
- Goals: correct EMS/DPD naming and pricing, pickup and courier mapping, rounding delivery price.
- Key commits: 706 (EMS fix), 787 (delivery names in app), 1060 (pickup store links → delivery services), 1065 (text tweaks), 567 (rounding to tens).
- Changed Areas and Impacts
  - `Order\Helper`: delivery type constants, pickup mappings, type map (pickup/delivery), rounding hook in `sale:onSaleDeliveryServiceCalculate`.
  - `ImShop` prepareDeliveryMethods/locations using store data.
  - Result: consistent delivery offerings in app/site, stable UX, reduced cognitive load.
- Intent/Why
  - Accuracy and clarity in shipping options, reduce basket drop-offs due to unclear delivery info.

### SEO/Meta/Sitemap/Robots
- Goals: prevent indexation of filter garbage; control meta robots; track goals.
- Key commits: 511 (robots exclusions), 989 (sitemap filter), 1031 (noindex follow for 4+ tags news), 1117/1120 (Yandex Metrika goals).
- Changed Areas and Impacts
  - `events/main/BeforeProlog.php` URL normalization, forced redirects, UTM saving.
  - `Seo` class usage in templates; template modifications for adding meta/canonical; robots rework.
  - Result: cleaner crawl/index footprint, improved analytics.
- Intent/Why
  - SEO hygiene, better SERP, more reliable analytics.

#### Robots.txt (1167)
- Disallow служебных разделов: `/bitrix/`, `/local/`, `/upload/`, `/ajax/`, `/api/`, `/my/` и пр.
- Явно Allow для ассетов, чтобы рендер поисковиков не ломался:
  - `/bitrix/*.js`, `/bitrix/*.css`
  - `/local/templates/trimiata/*.(css|js|svg|png|jpg|jpeg|webp|woff|woff2)`
  - `/upload/resize_cache/`
- `Clean-param` для UTM/служебных GET, `Crawl-delay: 1`.
- Агрессивные краулеры (Ahrefs, Semrush*, MJ12 и др.) — `Disallow: /`.

### Wishlist/Loyalty
- Goals: data hygiene for wishlist; loyalty surfaces (QR).
- Key commits: 1002 (remove inactive from wishlist; scheduled/cron fix), 1119 (QR on site/app), 968 (card type to segments).
- Areas: `Catalog\Helper`, cron scripts, `JWT`/auth for loyalty, templates.
- Impact: fewer dead items in UX, personalization via loyalty.

### Sessions/Infra/DevOps
- Goals: robust env, sessions in Redis, memcache for cache, dockerized workflows, CI to pull on commit.
- Key commits: 1016 (Redis sessions + fixes), memcache/env/docker series (2024‑02..05), 1084 (SSH keyscan, workflows), config sync & SSL.
- Areas: `system/server/compose.yaml`, `system/lib/nginx|php/Dockerfile`, `.settings.php` (reads `.env`), Makefile targets; GH workflows.
- Impact: stable environments, faster deploys, secure secrets; consistent `.env` usage via Dotenv.

---

### Cross-cutting File→Effect Heuristics
- Changes in `app/local/templates/trimiata/components/app/...` and `.../bitrix/...` imply front-end behavior/markup/UI of specific pages (catalog, news, menu, breadcrumbs, order list) changed.
- Changes in `app/local/php_interface/lib/app/*` imply domain logic shift (prices, catalogs, SEO, exchange), often paired with template changes for presentation.
- Changes in `app/api/*` indicate new/modified API endpoints (used by frontend or mobile app); check paired domain classes.
- Changes in `app/local/components/app/*` typically change controller/data prep logic for pages; templates in the theme folders render new state.

### Catalog URL policy (cheatsheet)
- Category: `/catalog/{category}/`
- Subcategory: `/catalog/{category}/{subcategory}/`
- Model: `/catalog/{category}/{subcategory}/{model}/`
- Subcategory URLs are clean segments; the smart filter path must exclude SUBCATEGORY when building the base URL for subcategory.

### Selected Commit Threads (sample with file hints)
- 1079 parts 1–6: frequent edits to `app/local/changes/template/src/js/app/AppSmartFilter.js`, `catalog.smart.filter` component and templates → incremental rollout of AJAX filter UX.
- 1085: broad changes in menu templates and `AppHeaderMenu.js` + category card styles → catalog navigation redesign; follow-up commits fix mobile behavior and H1.
- 705/742/747/756/744: `ImShop.php` + `api/webhook/*` + `Order\Helper` mappings → complete app order/delivery/payment/status pipeline; installId guard and source-based payment restrictions.
- 614/680: additions around payment webhooks and sending payment info to 1C; adjust PS IDs and payloads; ensure capture checks.
- 1060: move shops into delivery services and link pickup → `Order\Helper` mapping + templates/feeds for addresses.
- 848: mail on card payment → `Order\Helper::sendOrderEmail` tweaks; triggers on `sale` events.
- 1119: loyalty QR → `api/modal/loyalty-qr.php`, template hooks in account pages; JWT helper introduced.

### Practical Queries (how to recall quickly)
- Where to adjust app delivery titles or pickup locations?
  - `App\Order\Helper` (DELIVERY_TYPE_*), `ImShop::prepareDeliveryMethodsResult()`.
- How AJAX filter without reload is implemented?
  - `components/app/catalog.smart.filter*`, `app/local/changes/template/src/js/app/AppSmartFilter.js`, `catalog.full/main/baseBlocks/ajax_*`.
- How payment URL for ImShop/Tinkoff is created?
  - `ImShop::processPayment()` and PS mappings in `Order\Helper`.
- How statuses sync back to ImShop?
  - `Order\External\ImShop::sendOrderStatus()`; webhook endpoints under `api/webhook/*`.
- Where meta/canonical is set?
  - `Template\Helper::setMeta()` and before-prolog event `Main\BeforeProlog::initSeo()`.

### Notes and Assumptions
- Task IDs correlate with PRs/branches; merges into `stage` mark deployment batches.
- File patterns strongly suggest intent but verifying via code diff is advised before risky refactors.





### HL‑блоки и связи (сводка)
- `\CategoryTable` — ключи запросов: `=UF_ACTIVE => true`, `=UF_XML_ID => code`. URL: `/catalog/{code}/`.
- `\SubcategoryTable` — `=UF_ACTIVE`, `=UF_XML_ID`, `UF_CATEGORY` (FK). URL: `/catalog/{category}/{code}/`.
- `\ModelTable` — `=UF_ACTIVE`, `=UF_XML_ID`, `UF_CATEGORY`, `UF_SUBCATEGORY`. URL: `/catalog/{category}/{subcategory}/{code}/`.
- Индексы: стоит иметь индексы по `UF_XML_ID`, `UF_ACTIVE` во всех трёх HL.
- Дерево: `Catalog\Helper::getCategoriesTree()` собирает и помечает `SELECTED`, добавляет `PICTURES`, очищает дубликаты `clearCategoriesTree()`.

### AJAX‑пайплайн смарт‑фильтра (подтверждено кодом)
1) Клиент: `app/local/changes/template/src/js/app/AppSmartFilter.js → reload()`
   - Собирает значения, строит `smartParts`, отправляет `POST` на `SEF_FOLDER`.
   - Для категорий `action=categoriesFilter`, для обычных свойств — `smartFilter`/`smartFilterLink`.
2) Сервер до выполнения: `.../catalog.full/main/baseBlocks/ajax_before.php`
   - Обрабатывает `categoriesFilter`/`subcategoryFilter`: формирует чистый URL (`/catalog/{category}/` или `/catalog/{category}/{subA-i-subB}/`).
   - Исключает `SUBCATEGORY` из SMART_FILTER_PATH при наличии в базовом пути.
3) Сервер после: `ajax_after.php` — собирает HTML‑блоки ответа: `products`, `productsCount`, `smartfilter`, `title`, `modef` и др.
4) Клиент: применяет DOM‑диффы, пушит новый URL в history API, ре‑инициализирует элементы фильтра.
