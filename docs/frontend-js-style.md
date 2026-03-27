### Стиль фронтенд‑JS (Trimiata)

Цель: единый, предсказуемый паттерн модулей на базе глобального `App` и утилит (`AppApi`, `AppForm`, делегирование событий, data‑атрибуты).

#### Бэкенд/Компоненты и шаблоны (структура)
- Компоненты проекта живут в `app/local/components/app/{component}/` и включают:
  - `class.php` (логика, D7, `onPrepareComponentParams`, `executeComponent`, `prepareData`).
  - `component.php` только при необходимости (обычно не нужен при `class.php`).
  - `.description.php` (описание) и `.parameters.php` (параметры, опционально).
  - Внутренние шаблоны `templates/` используются как фоллбэк или для «технических» кейсов.
- Основные боевые шаблоны располагаются в site template: `app/local/templates/trimiata/components/app/{component}/{template}/`.
  - Базовый шаблон сайта для проекта: `{template} = main` (пример: `catalog.section/main`).
  - В шаблоне допускаются `result_modifier.php` и `component_epilog.php` (подготовка данных/добавочные скрипты).
- Обозначение путей/ролей:
  - Логика — строго в `class.php`; «тяжёлые» вычисления/ORM — здесь же, не в шаблоне.
  - Шаблон — только верстка и простые условия; никаких ORM.
  - Все селекторы в шаблоне — `data-role` для JS.

#### Базовый паттерн компонента
- Класс с именем `App{Feature}Component` в `class.php`.
- Поле `setFrameMode(true|false)` по задаче.
- `prepareData()` — собрать `arResult` плоскими структурами для шаблона; без дополнительных запросов в `template.php`.

#### Базовый паттерн js класса
- Класс с именем `App{Feature}` в виде: `const AppFeature = class AppFeature { ... }`.
- Поле `_isInitialized = false`, метод `isInitialized()` — для идемпотентной инициализации.
- В конструкторе сохраняй ссылку на `this.app = parent` и находи необходимые DOM‑элементы. Если контейнеров нет — верни `false` без ошибок.
- Подписка на инициализацию:
	- Если `!this.app || !this.app.isInitialized()` — слушай `document.addEventListener('AppInit', ...)` и запускай `initEvents()`.
	- Иначе — сразу вызывай `initEvents()`.

#### DOM и события
- Используй делегирование: `this.app.delegate(root, 'click|change|input', selector, handler)`.
- Все селекторы/классы вынеси в поля `elements = {...}`, `activeClasses = {...}`.
- Избегай прямых `querySelector` вне конструктора/методов поиска — храни ссылки на контейнеры.
- Для состояний/лоадеров: добавляй/снимай CSS‑классы `loading`/`active`; для кнопок — `App.showBtnLoading(btn)` с `_loaded` событием.

#### Динамический контент (modals/aside/CupertinoPane)
- Любой контент, подгружаемый через `App.loadAjaxAside()` или модалки, требует переинициализации после вставки в DOM.
- Используй `$.initialize('<selector>', callback)` для:
  - Слайдеров (`[data-role=detail-image-slider]` и связанные `thumbnails`) — запускать `app.initSlidesGallery(...)`.
  - Fancybox/zoom — инициализировать обработчики после появления `.fancybox__container`.
- Для модали быстрой покупки:
  - Контейнеру добавляется `mobile-pane__container__quick_buy`; к `.pane` — `pane_fast_buy`.
  - Открытие происходит по клику `data-role=product-show-offers`.

#### Замена контента вместо создания новых модалей
Канон и примеры см. в `docs/frontend-loaders.md` («Динамическая замена контента в модалях»). Кратко:
- Загружайте HTML и заменяйте `innerHTML` текущего контейнера;
- После замены — `$.initialize(container)` и `component.replaceButtons()`;
- Лоадер закрывайте через событие `_loaded` после полной вставки и инициализации.

#### Мобильная специфика CupertinoPane
Подробная специфика вынесена в `docs/frontend-loaders.md` («CupertinoPane (мобильные панели)»). Кратко: используйте контейнер `.mobile-pane__container__quick_buy`, после замены контента переинициализируйте обработчики с небольшим таймаутом; в пользовательских обработчиках кликов ставьте `e.stopPropagation()`.

#### Fancybox и динамический контент
После вставки HTML необходимо заново привязать Fancybox к новой разметке галереи:

```javascript
if (window.Fancybox) {
  window.Fancybox.bind('[data-fancybox="gallery"]', {});
}
```

Инициализацию вызывайте после `$.initialize(container)`.

#### HTTP/данные
- Запросы — только через `this.app.api` (`AppApi`):
  - `api.getJSON(url, 'POST', data, showToast)`; серверные формы — `AppForm`.
  - Для Bitrix Main Ajax: отправляй `{ c: '<component>', action: '<action>', mode: 'ajax', ...payload }` на `/bitrix/services/main/ajax.php`.
- Не используй `axios/fetch` напрямую в новых модулях.

#### Формы
- Для интерактивных форм — `new AppForm(containerId, params)`:
  - Валидатор на jQuery Validate: локализация ошибок, `.checked`/`.active` классы полей.
  - Кастомная отправка: `submitHandler` → свой метод отправки (через `AppApi`).
  - SmartCaptcha: используй встроенный в `AppForm` `checkSmartCaptcha()`/`send()`.

#### Мобильные состояния
- Адаптация по брейкпоинтам: сравнивай `window.innerWidth` с `this.app.breakpoint` (по умолчанию 768).
- Для мобильных панелей — `CupertinoPane` через `App.initCoopertinoPane()`.

#### Аналитика/метрики
- Яндекс.Метрика: `this.app.reachGoal('GOAL_CODE')` (не слать для dev/admin).
- Сервис‑воркер broadcast (кросс‑вкладки) — используй паттерн из `AppWishlist` при необходимости.

#### Нейминг/структура
- Сохраняй единый стиль:
  - Именование классов/методов — осмысленные глаголы/существительные.
  - Карта селекторов/классов в свойствах `elements`/`activeClasses`/`classes` (например, `classes.avatarMain`).
  - Минимизируй «магические строки» внутри логики.

#### Разметка (hooks)
- Для инициализации компонентов используем только стабильные data‑атрибуты, не id:
  - Например: `[data-role=account-profile-edit]` (ЛК → редактирование профиля), `[data-role=order-payment-retry]`, `[data-role=catalog-smart-filter]`, `[data-role=product-show-offers]` (быстрая покупка).
  - Все внутренние элементы также отмечаются `data-role`/`data-type` (см. `AppOrderPaymentRetry.elements`).
- Обновляемые через AJAX блоки имеют устойчивые контейнеры, которые заменяются целиком по data‑селекторам; после замены модуль переинициализирует события и диспатчит финальные события (`*_Loaded`).
- Не изменяй и не очищай служебные hidden‑поля (id, old_file и т.п.) в JS — серверная логика опирается на них. Меняй визуальное состояние и валидируемые поля формы.
- Формы личного кабинета (профиль) отправлять через AJAX, проверять sessid (заголовок X‑Bitrix‑Csrf‑Token или поле sessid), на успешный ответ — показывать toast/popover и снимать лоадер.

#### Бэкенд/API правила (коротко)
- Всегда используй `Ctx::request()`/`Ctx::server()`; не обращайся к `$_REQUEST/$_SERVER` напрямую.
- Выборка из D7 — через `getRow(['filter'=>..., 'select'=>[...]])` с явным списком полей (минимум данных, один запрос).
- Обновление пользователя — исключение: только через `\CUser->Update($id, $fields)`; для остальных сущностей используй D7 `::update`.
- Для API в `app/api/*`:
  - `require $_SERVER['DOCUMENT_ROOT'].'/api/core.php'` и создавай `$result = new ApiResult($request)`.
  - В модальных эндпоинтах повторно не подключать `core.php` (см. `app/api/modal/index.php`).
  - Проверяй: `$request->isAjaxRequest()`, `$request->isPost()`, `check_bitrix_sessid()`.
  - Белый список полей, никаких «сырых» массивов; UF‑поля — `$USER_FIELD_MANAGER->EditFormAddFields('USER', $fields)`.
  - Ответ — только JSON через `ApiResult::setData()->sendJsonResponse()`.

#### Пример мини‑скелета
```js
"use strict";
const AppFeature = class AppFeature {
  _isInitialized = false;
  elements = { container: '[data-role=feature]' };
  constructor(parent){
    this.app = parent;
    this.container = document.querySelector(this.elements.container);
    if (!this.container) return false;
    if (!this.app || !this.app.isInitialized()) {
      document.addEventListener('AppInit', () => this.initEvents());
    } else {
      this.initEvents();
    }
  }
  isInitialized(){ return this._isInitialized; }
  initEvents(){ /* делегирование, api, формы */ this._isInitialized = true; }
}
```

См. реализацию: `app/local/changes/template/src/js/app/AppNews.js`, `AppWishlist.js`, `AppAuthorization.js`, `App.js` (делегирование/утилиты), `AppForm.js`, `AppApi.js`.


