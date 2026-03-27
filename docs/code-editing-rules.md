## Правила редактирования кода (Trimiata)

Краткие договоренности по edits, соответствующие правилам проекта и общему стилю разработки.

### Базовые принципы
- EditorConfig: строго следуем `.editorconfig`. Не меняем стиль отступов (табы/пробелы) и ширину. Не «переформатируем» несвязанные фрагменты.
- Минимальные правки: атомарные edits под задачу. Без побочного рефакторинга/переездов.
- Чистота: не оставляем отладочные логи, закомментированный код, `TODO`. Именование — осмысленное.
- Секреты: никогда не коммитим ключи/токены. Конфиги — через `.env`/Bitrix Option.
- Ядро Bitrix не трогаем: правки только в локальных компонентах/шаблонах и доменных классах `app/local/*`.

### PHP/Bitrix
- Стиль: PSR‑12, типы, ранние возвраты, маленькие чистые функции. Отступы PHP — табами (см. `docs/architecture-map.md`).
- D7‑подход: ORM `Bitrix\Main\ORM`, события через `EventManager`, `Option::get/::set` — для настроек.
- Автозагрузка модулей: НЕ используем `Loader::includeModule()` в доменном коде — все модули Bitrix (sale, catalog, iblock и др.) загружаются автоматически через `inc/autoloadclass.php` при обращении к классам.
- Компоненты: разделяем логику (`class.php`) и шаблон (`template.php`).
- Кэширование: добавляя новые данные в `arResult`, не ломаем кеш-ключи. Если данные критичны для рендера — добавляем их в `SetResultCacheKeys([...])` и документируем инвалидацию.
- SEO/URL: при изменениях ЧПУ/фильтра — сверяемся с `docs/architecture-map.md` и `docs/knowledge-map.md`; канонический порядок поддерживает `Catalog\Helper::checkUriOrder*()`.

### JavaScript/Фронтенд
- Исходники JS: все JS‑модули находятся в `app/local/changes/template/src/js/` и компилируются через webpack в `app/local/templates/trimiata/dist/js/`.
- Структура исходников:
  - `app/local/changes/template/src/js/App.js` — главный модуль приложения
  - `app/local/changes/template/src/js/AppApi.js` — утилиты для API запросов
  - `app/local/changes/template/src/js/app/` — модули функциональности (`AppBasket.js`, `AppForm.js`, `AppSmartFilter.js` и т.д.)
  - `app/local/changes/template/src/js/modules/` — вспомогательные модули (`Scrollyeah.js` и т.д.)
  - `app/local/changes/template/src/js/Router.js` — роутер для lazy loading модулей
- Инициализация динамики: используем `$.initialize(selector, cb)` (см. `app/local/changes/template/src/js/App.js`).
- Bootstrap 5: предпочтительно data‑атрибуты (`data-bs-*`). Глобальная инициализация popover — `trigger: 'hover focus'` для `[data-bs-toggle=popover]`.
- Модульный стиль фронта: следуем `docs/frontend-js-style.md` (паттерн `App{Feature}`, делегирование событий, `AppApi`, `AppForm`). Минимизируем зависимости, не допускаем глобальных утечек.
- Адаптивность: поведение desktop/mobile — через Bootstrap‑классы (`d-none`, `d-md-flex` и т.п.) и медиа‑правила, а не UA‑детекцию.
- Сборка: не редактируем `dist`/минифицированные файлы. Источники правим в `app/local/changes/template/src/js/*` и собираем бандлы через webpack (см. ниже).

### Стили/Сборка фронта
- Где править: исходники в `app/local/changes/template/src/*` (SCSS/JS). Бандлы попадают в `app/local/templates/trimiata/{dist,bundle}`.
- Команды: `make build_front` или выборочно `make build_css` / `make build_js` (также см. `docs/runbook.md` и `docs/README.md`, «Сборка фронта»).

### Gitflow/Документация
- Ветки: `develop` — фичи, `hotfix` — критические фиксы от `main`. В `main` — только через PR (см. `docs/README.md`, «Gitflow»).
- Changelog: после задачи — обновляем раздел Unreleased в `docs/CHANGELOG.md` (формат Keep a Changelog + SemVer). Крупные темы — «Files → Effects → Intent». См. `docs/README.md`, раздел «Политика ведения Changelog».
- Commit summary для CI: формируем краткий текст и сохраняем в `.github/commit-summary.txt` (см. `docs/README.md`).

### Чек‑лист перед PR
- Форматирование соответствует `.editorconfig`. Дифф — только по сути задачи.
- Фронтенд‑исходники собраны: `make build_*` (если правились `src`).
- Визуальная проверка ключевых страниц; консоль браузера — без ошибок.
- Обновлены релевантные разделы документации (Changelog/архитектура/модель данных/knowledge map).

### Частые зоны правок (ориентир)
- Детальная/каталог: шаблоны в `app/local/templates/trimiata/components/app/*` и JS исходники в `app/local/changes/template/src/js/app/*` (компилируются в `app/local/templates/trimiata/dist/js/`).
- API/модалки: `app/api/*` (пример: `app/api/modal/weight.php`). Ответы — через `ApiResult`.
- Фильтр/ЧПУ: `App\Catalog\Helper`, компоненты `catalog.full`, `catalog.smart.filter*`, JS `app/local/changes/template/src/js/app/AppSmartFilter.js`.

### Пример (popover «Вес изделия»)
- Разметка: ссылка `question_tooltip` рядом с «Вес», атрибуты `data-bs-toggle="popover"`, `data-bs-title`, `data-bs-content`.
- Инициализация: глобально в `App.js` для `[data-bs-toggle=popover]` с `trigger: 'hover focus'` (hover на desktop, фокус для доступности).


