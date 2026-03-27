## Runbook (Trimiata, Bitrix D7)

### Быстрый старт (Dev)
1) Подготовьте `app/.env` (см. примеры ключей ниже).
2) Убедитесь, что есть каталоги ядра и аплоада, и пропишите пути:
   - `BITRIX_CORE_PATH=/abs/path/to/app/bitrix`
   - `UPLOAD_FOLDER_PATH=/abs/path/to/app/upload`
3) Запуск контейнеров:
   - `make startd` — поднять в фоне (или `make start` в foreground).
   - Открыть `http://localhost:8080`.
4) Остановка/перезапуск:
   - `make stop` / `make restart`.

Makefile использует `system/server/compose.yaml` и читает `COMPOSE_PROJECT_NAME`, `BITRIX_CORE_PATH`, `UPLOAD_FOLDER_PATH` из `app/.env`.

### .env (минимум для запуска)
- БД/кеш (см. `system/server/data/bitrix/.settings.php`):
  - `DB_HOST`, `DB_NAME`, `DB_LOGIN`, `DB_PASSWORD`
  - `MEMCACHED_HOST`
- Флаги окружения:
  - `IS_DEV=1`, `COMPOSE_PROJECT_NAME=trimiata_dev`
- Пути (важно для docker compose):
  - `BITRIX_CORE_PATH=/abs/path/to/app/bitrix`
  - `UPLOAD_FOLDER_PATH=/abs/path/to/app/upload`

### Сборка фронта
- Исходники: `app/local/changes/template`.
- Команды:
  - `npm ci && npm run build` — собрать ассеты в `app/local/templates/trimiata/{dist,bundle}`.
  - Альтернатива из README: `make build_front` / `make build_css` / `make build_js` (если настроено в окружении).

### Частые точки правок (ориентир)
- ЧПУ/роутинг каталога: `app/local/components/app/catalog.full/class.php` (`getUrlTemplates()`, `prepareData*`).
- AJAX фильтры до/после: `.../catalog.full/main/baseBlocks/ajax_before.php` → `ajax_after.php`.
- Клиентский фильтр: `app/local/changes/template/src/js/app/AppSmartFilter.js` (исходник, компилируется в `app/local/templates/trimiata/dist/js/`).
- Нормализация URL и форс‑редиректы: `lib/events/main/BeforeProlog.php` (`init()`, `checkForceRedirects()`).
- Формирование ссылок фильтра: `App\Catalog\Helper::{getCurrentFilterLinkParams,getLinkByParams,checkUriOrder*}`.
- Дерево категорий/подвидов/моделей: `App\Catalog\Helper::getCategoriesTree()`.
- SEO/Canonical/OG: `App\Template\Helper::setMeta()` + `BeforeProlog::initSeo()`.

### QA‑чеклист после изменений каталога/фильтра
- Страницы:
  - Категория: `/catalog/{category}/` → 200, хлебные корректны.
  - Подкатегория: `/catalog/{category}/{subcategory}/` → 200.
  - Модель: `/catalog/{category}/{subcategory}/{model}/` → 200.
- Множественный выбор в сегменте (`-i-`) работает, порядок параметров стабилен (301 на канон).
- При кликах в фильтре:
  - AJAX обновляет блоки: товары, счётчик, H1, быстрые ссылки, фильтр, URL в address‑bar.
  - Кнопка `modef` показывает корректную ссылку на фильтр (`FILTER_URL`).
- Цена (прогрессивный слайдер):
  - Для URL `/catalog/.../tsena-ot-1164-do-218350/` бегунки стоят ровно на 1164 и 218350
  - Для URL с «неровными» значениями (например, 21001) — значения попадают в шкалу и бегунки отображают их точно
  - Шкала в «густых» ценовых диапазонах плавнее (меньше шаг); шаблон передаёт индексы `data-indexes`
- Нет утечек секретов/токенов в репозитории, `.env` вне VCS.

### Полезные команды
- Импорт каталога (пример): `make import` (выполнит PHP‑скрипт в контейнере).
- Установка PHP‑зависимостей: `make composer_install` (см. README).

### Git/GitHub
- См. руководство: `docs/git-and-github.md` — подключение по SSH, проверка доступа, базовые операции и подтверждение прямого доступа ИИ‑ассистента к GitHub для чтения веток/PR.

### Скрипты изменений БД (app/local/changes/db)
- Расположение: `app/local/changes/db/{task_id}/script.php` (допускаются дополнительные файлы `script2.php`, именованные миграции).
- Стиль:
  - Всегда подключайте `prolog_before.php` и необходимые модули (`Loader::includeModule`).
  - Скрипт должен быть идемпотентным: повторный запуск не должен падать и должен безопасно пропускать уже применённые изменения.
  - Никаких интерактивных шагов; по завершении выводите краткий отчёт (`print_r`/`pre`) и `die();`.
  - Для HL‑блоков используйте `CUserTypeEntity` для добавления UF‑полей; при необходимости инициализируйте значения через `Application::getConnection()`.
- Запуск: внутри контейнера PHP или на стенде, например:
  - `php -d short_open_tag=1 app/local/changes/db/{task_id}/script.php`
- Откат: при наличии риска изменений данных публикуйте соседний `rollback.php` с логикой отката.
