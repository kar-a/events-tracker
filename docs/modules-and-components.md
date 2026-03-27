## Инвентарь модулей и компонентов

### Компоненты `app:*`
- catalog.full — маршрутизация каталога, хлебные, SEO, AJAX‑блоки
- catalog.smart.filter — умный фильтр свойств
 - catalog.smart.filter — умный фильтр свойств (прогрессивный слайдер цены: `buildPriceHistogram()`, константы `PRICES_HISTOGRAM_*`)
- catalog.smart.filter.categories — фильтр категорий/подвидов (AJAX: categoriesFilter/subcategoryFilter)
- catalog.element — карточка товара (детальная/compact для быстрой покупки)

### Модули
### Интеграции (практика)
- Шаблон: `Service` (curl/__call) + фасад.
- Слои:
  - `app/local/php_interface/lib/app/<ServiceName>/Service.php`
  - `app/local/php_interface/lib/app/<ServiceName>/<Facade>.php`
- Вебхуки API: подключают `app/api/core.php` (`ApiResult`).
- Хранение данных интеграций: отдельные папки `upload/<service>/`.

#### ImShop — поиск/фильтры
- Контроллер: `App\Order\External\ImShop`.
- Поиск (`search`) и фильтры (`filter`) используют общие помощники:
  - `normalizeSearchTerm()` — нормализация запроса (артикулы/штрихкоды).
  - `includeSearchPage($query)` — получение ID результатов через компонент `app:search.page` с `FILTER_NAME=arrAddFilter`.
  - `buildImShopPropertyFilter($applied)` — построение Bitrix‑фильтра по свойствам: directory→`UF_XML_ID` (через `ImportHelper::getCode`), LIST→enum ID, Number/String→значения в `PROPERTY_*`.
  - `refineElementsByFilters($ids,$applied)` — пост‑фильтрация ID и применение диапазона цен по `q_price` (через `PRICE_{APP_PRICE_ID}`).
  - `computeSlicePriceRange($ids,$propFilter)` — вычисление min/max цены по текущему срезу (крайние элементы по `PRICE_{APP_PRICE_ID}`).
- Контракты:
  - Цена в фильтрах ImShop — идентификатор `q_price`.
  - Ценовые условия формируем только по `PRICE_{APP_PRICE_ID}` (не `CATALOG_PRICE_*`).
- local/modules/opensource.order — модуль оформления заказа

### Правила создания компонентов `app:*`

#### Обязательная структура файлов

1. **Файл класса**: `app/local/components/app/{component}/class.php`
   - Наименование класса: `App{Component}Component` в PascalCase (например, `AppCatalogWishlistLinkComponent`)
   - Наследует: `CBitrixComponent`
   - Обязательные методы:
     - `onPrepareComponentParams($params)` — валидация и нормализация параметров
     - `executeComponent()` — точка входа: загрузка модулей, обработка действий, вызов `includeComponentTemplate()`
     - `checkModules()` — проверка загрузки необходимых модулей (через `Loader::includeModule`)
   - Опциональные методы:
     - `prepareData()` / `doAction()` / `actionView()` — подготовка данных для `arResult`
   - PHPDoc: каждый метод сопровождаем кратким PHPDoc («что делает», параметры, `@return`)
   - Перед `return` всегда ставим пустую строку

2. **Файл описания**: `app/local/components/app/{component}/.description.php`
   - Формат: ассоциативный массив `$arComponentDescription`
   - Обязательные ключи:
     - `NAME` — краткое название компонента
     - `DESCRIPTION` — описание назначения
     - `CACHE_PATH` — `'Y'` (поддержка кеширования)
     - `SORT` — порядок сортировки (обычно `10`)
     - `PATH` — путь в админке:
       ```php
       'PATH' => [
           'ID' => 'other',  // или 'app' для сложных компонентов
           'NAME' => 'Разное'
       ]
       ```
   - Опциональные ключи:
     - `ICON` — путь к иконке (обычно пустая строка)

3. **Файл параметров**: `app/local/components/app/{component}/.parameters.php` (опционально)
   - Нужен только если компонент принимает параметры от редактора страниц
   - Использует `CIBlockParameters::Get*` для генерации списков
   - Определяет массив `$arComponentParameters` с группами параметров
   - Примеры: `catalog.section.groups/.parameters.php`, `catalog.smart.filter/.parameters.php`
   - Если компонент не имеет настраиваемых параметров — файл отсутствует

4. **Пустой шаблон fallback**: `app/local/components/app/{component}/templates/.default/template.php`
   - Пустой файл или минимальная заглушка
   - Используется Bitrix как резервный шаблон
   - **Обязательно присутствует**, даже если не используется

5. **Боевой шаблон**: `app/local/templates/trimiata/components/app/{component}/main/`
   - Файлы шаблона:
     - `template.php` — основная разметка (обязательно)
     - `result_modifier.php` — подготовка данных для шаблона (опционально)
     - `component_epilog.php` — дополнительные скрипты/мета после рендера (опционально)
   - В `template.php` НЕТ ORM/сложной логики: только верстка, условия, `data-role` для JS
   - Все вычисления и загрузка данных — в `class.php` или `result_modifier.php`
   - Эпилог НЕ рассчитывает данные — использует готовые структуры из `result_modifier.php`

#### Паттерны кода

- **Imports**: группируем в блок `use`, сортируем: Bitrix → внешние → App
- **D7 ORM**: читаем через `::getRow(['filter'=>..., 'select'=>[...]])` с минимумом полей
- **Обновление данных**: для пользователя — `\CUser->Update` (исключение), для остальных — D7 `::update`
- **Булевые UF**: нормализуем в `'1'`/`'0'` в `prepareData()`
- **Frame mode**: `setFrameMode(true|false)` в `executeComponent()` по задаче
- **Обработка ошибок**: через `try/catch`, ошибки в `arResult['ERROR']`

#### Шаблоны компонента

- Основной шаблон живёт в `app/local/templates/trimiata/components/app/{component}/main/`
- Допустимы `result_modifier.php` (подготовка данных) и `component_epilog.php` (скрипты/мета)
- В `template.php` нет ORM/сложной логики: только верстка, условия, `data-role` для JS
- Все вычисления и загрузка данных — в `class.php` (или `result_modifier.php`)
- Эпилог использует готовые структуры из `result_modifier.php` (например, JSON‑LD ItemList)
- PHPDoc: каждый метод класса с кратким описанием, параметрами, `@return`; перед `return` пустая строка

#### Инициализация и маршрутизация

- `setFrameMode(true|false)` по задаче
- Маршрутизация/страничный контекст через `App\Template\Helper::initPageType()` в `BeforeProlog`

### Быстрая покупка (Quick Buy) и COMPACT_MODE
- Эндпоинт: `app/api/modal/product-quick-buy.php` (рендер `app:catalog.element` с параметрами `COMPACT_MODE=Y`, `QUICK_BUY=Y`; заголовок offcanvas пустой, чтобы скрыть `offcanvas-title`).
- Вызов (frontend): из `AppBasket.js` по клику `data-role=product-show-offers` → `app.loadAjaxAside('product-quick-buy','api',{elementId,template})`.
- Мобильная панель: к контейнеру добавляется класс `mobile-pane__container__quick_buy`; к родительскому `.pane` — `pane_fast_buy`.
- Шаблон `catalog.element/main` при `COMPACT_MODE=Y`:
  - Добавляет класс `product_compact` к `div.product row`.
  - Скрывает основной `product_gallery__slider`; остаются `product_gallery__thumbnails` с Fancybox.
  - В `component_epilog.php` скрывает тяжёлые блоки: «Комплекты», «Недавно просмотренные», `product__whatsapp_link`, `product__delivery_container`.
- Динамическая инициализация: для контента, подгружаемого в aside/pane, использовать `$.initialize` в `App.js` для слайдера (`[data-role=detail-image-slider]`) и zoom Fancybox — иначе обработчики не навешиваются.

### Подборки (Collections)
- Данные: HL‑блок `Collections` (`CollectionsTable`) с полями: `UF_XML_ID` (slug коллекции, генерируется из `UF_NAME` по `ImportHelper::getCode()`), `UF_EXTERNAL_CODE`, `UF_NAME`, `UF_LOCATION`, `UF_ITEMS` (множественные артикулы CML2).
- Импорт (1174): коллекции подтягиваются из 1С; `UF_XML_ID` создаётся из имени; `UF_ITEMS` содержит артикулы, по которым будет строиться фильтр.
- URL: страницы коллекций открываются по `/catalog/{UF_XML_ID}/`.
- Компонент: `app:catalog.full` распознаёт коллекции в `getAdditionalUrlTemplates()` и передаёт данные в `arResult['COLLECTION']`.
- Фильтр/SEO: фактическое построение фильтра выполняется в `prepareDataCollection()` — фильтрация по артикулам коллекции через `=PROPERTY_CML2_ARTICLE`; 404, если коллекция не найдена или список `UF_ITEMS` пуст; заголовки/description формируются из `COLLECTION.NAME`.
- Шаблон: `components/app/catalog.full/main/collection.php` подключает общий шаблон `sections.php` (единый layout/UX сортировок, списка, быстрых ссылок). Специфика коллекции остаётся в серверной подготовке данных.

### Подборки (Collections)
- Данные: HL‑блок `Collections` (`CollectionsTable`) с полями: `UF_XML_ID` (slug коллекции, генерируется из `UF_NAME` по `ImportHelper::getCode()`), `UF_EXTERNAL_CODE`, `UF_NAME`, `UF_LOCATION`, `UF_ITEMS` (множественные артикулы CML2).
- Импорт (1174): коллекции подтягиваются из 1С; `UF_XML_ID` создаётся из имени; `UF_ITEMS` содержит артикулы, по которым будет строиться фильтр.
- URL: страницы коллекций открываются по `/catalog/{UF_XML_ID}/`.
- Компонент: `app:catalog.full` распознаёт коллекции в `getAdditionalUrlTemplates()` и передаёт данные в `arResult['COLLECTION']`.
- Фильтр/SEO: фактическое построение фильтра выполняется в `prepareDataCollection()` — фильтрация по артикулам коллекции через `=PROPERTY_CML2_ARTICLE`; 404, если коллекция не найдена или список `UF_ITEMS` пуст; заголовки/description формируются из `COLLECTION.NAME`.
- Шаблон: `components/app/catalog.full/main/collection.php` подключает общий шаблон `sections.php` (единый layout/UX сортировок, списка, быстрых ссылок). Специфика коллекции остаётся в серверной подготовке данных.

#### Динамическая замена контента в быстрой покупке
Каноническая инструкция и примеры кода перенесены в документ:

- `docs/frontend-loaders.md` → разделы: «Динамическая замена контента в модалях», «CupertinoPane (мобильные панели)», «Лучшие практики».

Кратко:
- Не создавайте новые offcanvas/CupertinoPane при смене свойств; заменяйте содержимое текущего контейнера.
- Используйте событийную модель лоадеров: `app.showLoader('_loaded')` → после вставки/инициализации `document.dispatchEvent(new Event('_loaded'))`.
- После замены: `$.initialize(container)` и `component.replaceButtons()`; на мобильном — дополнительная переинициализация обработчиков с небольшим таймаутом.

### Связи компонентов, шаблонов и JS
- JS‑модули находятся в `app/local/changes/template/src/js/app/*` и компилируются через webpack в `app/local/templates/trimiata/dist/js/`.
- Модули строятся по паттерну `App{Feature}` и инициализируются из `App.js` (`app/local/changes/template/src/js/App.js`).
- Инициализация и селекторы — только по `data-role` (не id). Шаблоны обязаны размечать узлы `data-role` для устойчивости к перерисовкам.
- Частичные обновления — контракты `{ html, script, storage }` (см. `AppApi`); после вставки выполняется `script`, пишется `storage`, диспатчатся события `*_Loaded`.
- В шаблонах — никаких ORM/сетевых обращений; подготовка данных — `class.php`/`result_modifier.php`.

### API (app/api/*) — правила
- Инициализация: `require $_SERVER['DOCUMENT_ROOT'].'/api/core.php'`, `$request = Ctx::request()`, `$result = new ApiResult($request)`.
- Для модальных эндпоинтов (`app/api/modal/*`) повторно не подключаем `core.php` (уже подключён в `api/modal/index.php`).
- Проверки: `$request->isAjaxRequest()`, `$request->isPost()`, `check_bitrix_sessid()`.
- D7 ORM: чтение через `getRow(['filter','select'])`; минимизируй набор полей.
- Обновление пользователя — только `\CUser->Update` (исключение); diff‑only: формируй `$fields` только из реально изменённых значений.
- UF‑коды — из `App\User\Helper` констант (единая точка правды).
- Ответ — JSON через `ApiResult::setData()->sendJsonResponse()`; ошибок — через `$result->addError(new Error(...))`.

### Frontend JS — краткие нормы
- Исходники JS: `app/local/changes/template/src/js/` (компилируются через webpack в `app/local/templates/trimiata/dist/js/`).
- Модульная структура: глобальный `App` + классы `App{Feature}`; делегирование `App.delegate`;
- Состояния: `App.showBtnLoading`/`_loaded` и `App.showBlockLoading`;
- Формы ЛК отправлять через AJAX (`AppApi`) c sessid (заголовок X‑Bitrix‑Csrf‑Token уже проставляется);
- В шаблонах — только `data-role`; без вычислений, ORM и сторонних вызовов.
- Lazy loading: модули загружаются по требованию через `Router.modules.X()` и `Router.assets.X()` (см. `app/local/changes/template/src/js/Router.js`).

#### Превью видео (VIDEO_PREVIEW)
- Генерация вынесена во внешнюю систему. Внутри проекта только адресуем готовые изображения.
- Именование файла превью: берём часть имени видео до первого символа `_`, добавляем суффикс `_preview.jpeg`.
  - Пример: `123_v1.mp4` → `123_preview.jpeg`.
- Размеры: `Template\Helper::VIDEO_PREVIEW_SIZES` равен `VIDEO_SIZES` плюс `smallest=100`.
  - Набор: `big=1080`, `detail=700`, `preview_big=500`, `preview=260`, `smallest=100`.
- URL‑схема (резайзер внешней системы): `https://{MEDIA_STORE_HOST}/RESIZED/PREVIEW/{size}x{size}/{file}`.
- Хелперы (расположение): `App\Catalog\Helper`
  - `getVideoPreviewFileName(string $video): string` — формирует имя превью.
  - `getProductVideoPreviewImages(array $product, bool $addProtocol=false): array` — строит массив URL по ключам размеров.
  - `getProductAdditionalData(&$product)` дополняет `$product['VIDEO_PREVIEW']`.
- Использование в карточке товара (`catalog.element`):
  - В `result_modifier.php` выбираем постер: `detail` → `VIDEO_PREVIEW['detail'][0]`, миниатюру: `preview` → `VIDEO_PREVIEW['preview'][0]`, с фолбэком на фото товара.
  - В шаблоне (`template.php`) постер подставляется в атрибут `poster` тега `<video>`, миниатюра — в превью‑кнопку видео.
  - Если превью отсутствуют — UI устойчиво падает на `PICTURES`.
- QA: проверить, что все ожидаемые размеры присутствуют на хранилище; при смене имени видео подтверждать наличие соответствующего файла `_preview.jpeg`.

#### SMS (Beeline)
- Поток: доменная точка — `App\Sms::send($phone,$message)`; проверяет/нормализует номер, отправляет через провайдера, при неудаче пишет в стек `SmsStackTable`; фоновая отправка — `App\Sms::moveStack()`.
- Провайдер: `App\Sms\Beeline\Service` (HTTP, логин/пароль, `APP_BEELINE_SMS_LOGIN`/`APP_BEELINE_SMS_PASS`). Endpoint: `https://a2p-sms-https.beeline.ru/proto/http/rest`. Тело: JSON `{ user, pass, action: 'post_sms', target: '+phone', message }`.
- Фасад: `App\Sms\Beeline\Beeline` (вызывает `Service::proto/http/rest`).
- Точки вызова:
  - Авторизация: `App\User\Auth\Auth` (код входа) → `Sms::send()`.
  - `lib/events/main/OnBeforeEventSend.php` — сценарии отправки уведомлений.
- История: `StreamTelecomSms` удалён/заменён на Beeline (task 1185).

### Изображения/видео (напоминание)
- Все расчёты перенести в `result_modifier.php` и/или хелперы (`Template\Helper`).
- Списки: `IMAGES_LIST`/`IMAGE` на элементе; Детали/быстрая покупка: `IMAGES_LIST` на `arResult`.
- Ключи: `SRC`, `SRCSET`, `SIZES`, `ALT`, `TITLE`; `SRCSET` из `IMAGE_SIZES`; `SIZES` через `TemplateHelper::getListImageSizes()`.
- Для lazyload используем `class=lazyload`, `data-src`, `data-srcset`, `data-sizes`.
- Для Media Store URL не используем `CFile::ResizeImageGet` (не Bitrix FileID) — берём предсобранные размеры.

### Самосовершенствование (правило)
- При каждом изменении компонента/шаблона/JS:
  - Проверяй соответствие правилам выше (D7, diff‑only обновления, отсутствие логики в шаблонах, `data-role`).
  - Если обнаружил несовместимость (ORM/логика в шаблоне, UF коды «вручную», суперглобальные) — вынеси в `class.php`/`Helper`, замени на `Ctx::request()`/константы, и дополни `docs/*` правилами.
  - Фиксируй новые инварианты в документации (`docs/modules-and-components.md`, `docs/frontend-js-style.md`) и используй их как чек‑лист при ревью.

### Инвентарь модулей и компонентов

#### Модули (`app/local/modules/*`)
- `opensource.order` — внешний модуль оформления заказа
  - Устанавливает компонент `opensource:order` (см. `install/components/...`).
  - Либы: `lib/{orderhelper.php, locationhelper.php, errorcollection.php}`.
  - Установка/удаление — `install/index.php` (регистрация модуля и копирование компонентов).

#### Компоненты (`app/local/components/*`)
- Префикс `app:` — внутренняя витрина:
  - Каталог: `catalog.full`, `catalog.section`, `catalog.element`, `catalog.smart.filter*`, `catalog.section.list`, `catalog.element.offers`, `catalog.sorter`, `catalog.wishlist.link`.
  - Фильтры каталога: `app:catalog.smart.filter`, `app:catalog.smart.filter.categories`.
  - Новости/поиск: `news.full`, `news.tags.list`, `search.page`.
  - ЛК/заказы/корзина: `sale.basket.small`, `sale.basket.popup`, `sale.orders.current.link`, `sale.personal.order.list`, `system.auth.full`, `subscribe.form`, `sale.payment.retry`.
  - Гео: `location.search`, `location.change.list`.
  - Прочее: `banners`.
- Префиксы `opensource:`/`trimiata:`/`account:` — см. соответствующие каталоги.


#### Шаблон сайта (`app/local/templates/trimiata`)
- `header.php`, `footer.php` — инициализация ассетов, UI и JS-модулей, блоки меню/поиска, корзины и т.д.
- `components/*` — оверрайды шаблонов Bitrix и собственных компонентов.
- `dist/`, `bundle/` — собранные ассеты; исходники в `local/changes/template`.

#### Шаблоны: изображения (паттерн)
- Расчёты и подготовку данных изображений выполняем в `result_modifier.php`, а не в `template.php`.
- На каждом элементе формируем массив `IMAGES` с ключами: `SRC`, `SRCSET`, `SIZES`, `ALT`, `TITLE`.
- `SRCSET` строим из `Template\\Helper::IMAGE_SIZES` (без хардкода списка), базовый `SRC` — `big` (fallback: `detail`).
- Для иконок категорий в меню: `SIZES = TemplateHelper::IMAGE_SIZES['small'] . 'px'`.
- Для lazyload используем `class=lazyload`, `data-src`, `data-srcset`, `data-sizes`.
- Для Media Store URL не используем `CFile::ResizeImageGet` (не Bitrix FileID) — берём предсобранные размеры.

#### События/хуки
- `lib/events/main/BeforeProlog.php` — нормализация URL, SEO, UTM, сканеры, инициализация `Ctx` и типа страниц.
- `lib/events/sale/Order.php` — обработчики заказов (генерация номеров, оплата, статусы, письма, ecommerce, интеграции 1С/ImShop, округления доставки).
- `lib/events/*` также содержит хендлеры: `iblock`, `search`, `fileman`, `yandexmarket` и т.п.


