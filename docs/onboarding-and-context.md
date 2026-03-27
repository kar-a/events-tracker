## Онбординг **events.trimiata.ru**

### Цели
- Быстро понять **поток событий**: клиенты → HTTPS → collector → ClickHouse → джобы / Grafana / Redis.
- Не путать с каталогом Bitrix: в **`system/events-service/`** нет `app/catalog.full` и нет шаблона «Тримиата».

### План ознакомления (≈30–60 мин)
1) Документы (10–15 мин)
   - [README.md](../README.md) в корне репозитория и [docs/README.md](./README.md).
   - [architecture-map.md](./architecture-map.md), [build-and-ops.md](./build-and-ops.md).
   - [system/events-service/docs/STRUCTURE.md](../system/events-service/docs/STRUCTURE.md), [system/events-service/docs/architecture.md](../system/events-service/docs/architecture.md), [system/events-service/docs/event-contract.md](../system/events-service/docs/event-contract.md).
   - [CHANGELOG.md](./CHANGELOG.md) (Unreleased — строки про events/collector).
2) Запуск локально (10 мин)
   - `cd system/events-service`, `cp infra/compose/.env.example infra/compose/.env`, `make up` (см. [system/events-service/README.md](../system/events-service/README.md)).
   - Проверка: healthcheck‑скрипты из `system/events-service/scripts/`, дашборды Grafana при необходимости.
3) Контракт (10 мин)
   - `system/events-service/packages/contract/` — JSON Schema, типы, тест‑вектора.
4) Связь с сайтом (5 мин)
   - Как сайт/app шлют события: см. задачи в CHANGELOG (например, `App.raiseEvent` / черновик диспатча на events); политика URL каталога **не** задаётся в collector.

### Оперативная разведка (grep / поиск)
- `collector-node`, `POST /v1/events`, `events_raw`, `packages/contract`.
- Docker: `system/events-service/infra/compose/`, `Makefile`.

### Память и артефакты
- Задачи по **витрине рекомендаций и схеме CH** — в [CHANGELOG.md](./CHANGELOG.md) и в коде под `system/events-service/`.
- Исторические заметки про **каталог Bitrix** — в [knowledge-map.md](./knowledge-map.md) и [memory/INDEX.md](./memory/INDEX.md) (в основном про сайт).

### Definition of Ready (events)
- Понятен контракт поля события, известны эндпоинт ingestion и переменные окружения compose; есть план инвалидации/ретенции при работе с ClickHouse.

---

## План онбординга и получения контекста (основной сайт Bitrix, Trimiata)

См. также указатель [legacy-main-site/README.md](./legacy-main-site/README.md). Этот раздел нужен, если вы правите **trimiata.ru** (PHP, компоненты, смарт‑фильтр).

### Цели
- Быстро получить достаточный контекст для безопасных edits с минимальным риском.
- Синхронизироваться с URL/SEO политикой и смарт‑фильтром.
- Построить устойчивую «память» по проекту для ускорения последующих задач.

### Чёткий план ознакомления (итерация ≤30–60 мин)
1) Документы (5–10 мин)
   - Прочитать: `docs/project-overview.md` (раздел про сайт), `architecture-map.md`, `modules-and-components.md`, `data-model.md`, `integrations.md`, `runbook.md`, `security-and-quality.md`.
   - Просмотреть `docs/CHANGELOG.md` (Unreleased + последний релиз) и `docs/knowledge-map.md`.
2) Вход и нормализация URL (5 мин)
   - Код: `app/local/php_interface/lib/events/main/BeforeProlog.php` → `init()`, `checkForceRedirects()`, `initSeo()`.
   - Проверить соответствие правилам из `docs/architecture-map.md` (end‑slash, lowercase, `/page-N/`).
3) Каталог/роутинг (5–10 мин)
   - Компонент: `app/local/components/app/catalog.full/class.php::getUrlTemplates()/prepareData()`.
   - Шаблоны: `app/local/templates/trimiata/components/app/catalog.full/main/*`.
4) Смарт‑фильтр (10 мин)
   - Клиент: `app/local/changes/template/src/js/app/AppSmartFilter.js` (`reload()` и action'ы, исходник, компилируется через webpack).
   - Сервер: `.../catalog.full/main/baseBlocks/ajax_before.php` → `ajax_after.php`.
   - Хелпер: `App\Catalog\Helper::{getLinkByParams,checkUriOrder,checkUriOrderAndRedirect}`.
5) Интеграции ImShop/1C (5–10 мин)
   - Вебхуки: `app/api/webhook/*`, 1C: `app/api/1c/*`.
   - Домен: `App\Order\External\ImShop`, очередь обмена `App\Exchange::*`.
6) Быстрая верификация окружения (опционально)
   - `Makefile` цели, `system/server/compose.yaml`, `.env` ключи (см. `runbook.md` — раздел про сайт).

Результат: сформирована карта маршрутов, фильтра, точек входа и интеграций; известно, куда вносить минимальные правки и какие правила не нарушать.

### Быстрые способы получения контекста (оперативная разведка)
- Семантический поиск: «Как работает X? Где Y? Кто формирует Z?» по директории `app/*`.
- Шаблонные запросы:
  - «Где нормализуются URL/редиректы OnBeforeProlog?»
  - «Где определён getUrlTemplates() каталога?»
  - «Где кодируются smart‑parts и кто выполняет 303/301?»
  - «Где строится canonical/meta?»
  - «Где инициализируется/оплачивается заказ в ImShop?»
- Греп‑паттерны на один файл: `checkUriOrder\(|LocalRedirect\(|getUrlTemplates\(|AppSmartFilter`.
- Кросс‑сверка с `CHANGELOG.md` (Files → Effects → Intent) для нахождения узлов изменений.

### Память: что сохранять и как использовать
1) Инварианты (никогда не ломать)
   - Политика URL каталога и исключение `SUBCATEGORY` из `SMART_FILTER_PATH`.
   - Канонический порядок фильтра через `checkUriOrder*`.
   - Нормализация URL до роутинга (lowercase, end‑slash, pagination).
2) Якорные файлы
   - `BeforeProlog.php`, `catalog.full/class.php`, `.../ajax_before.php`, `.../ajax_after.php`, `app/local/changes/template/src/js/app/AppSmartFilter.js`, `Catalog/Helper.php`, `Template/Helper.php`.
3) Чек‑листы при edits
   - URL/SEO: canonical, 301, `getUrlTemplates()`/AJAX‑ветки синхронны, `Seo::getPropsRegulars()` не хардкодим.
   - Смарт‑фильтр: `categoriesFilter/subcategoryFilter/smartFilter*` возвращают ожидаемые блоки.
   - Интеграции: вебхуки возвращают корректный контракт, не ломаем очередь обмена.
4) Хранилища памяти
   - Постоянная: `.cursor/rules/*`, `.cursor/presets/*`, `docs/knowledge-map.md` (узлы/эффекты), `docs/CHANGELOG.md` (связки Files→Effects→Intent).
   - Оперативная: заметки ассистента (инструмент памяти), ссылки на узлы кода и риски.

### Расширенный план ознакомления (для нетипичных задач)
1) Профилирование точки задачи: найти entry‑point, связанный helper и события D7.
2) Проверить кеш‑границы и ключи `arResult` в компонентах.
3) Изучить конфиги `Ctx::config()` под домен задачи (`config/*.php`).
4) Просмотреть последние 20–50 записей в `CHANGELOG.md` по затронутой теме и сопоставить файлы.
5) Сформировать минимальный план edits с проверками и быстрым откатом.

### Acceptance (Definition of Ready)
- Известно, где править; известны правила URL/SEO/фильтра; подготовлен mini‑QA чек‑лист для конкретной задачи.

### Быстрый QA‑чеклист после изменений каталога/фильтра
- Категория/подкатегория/модель открываются (200), порядок фильтра канонизируется (301 при необходимости).
- AJAX обновляет товары/счётчик/фильтр/быстрые ссылки; `modef`/`FILTER_URL` верны.
- Canonical/meta выставляются, нет дублей URL, нет утечек секретов.

