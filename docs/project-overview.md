## Обзор проекта (Bitrix, Trimiata)

### Стек и директории
- PHP/Bitrix (D7), Docker/Compose, Nginx/PHP‑FPM, MySQL, Redis
- `app/` — DocumentRoot; ядро в `app/bitrix`
- `local/php_interface/lib/app/*` — доменная логика
- `local/components/*` — компоненты
- `local/templates/trimiata` — шаблон и фронт

### Веб‑входы и конфиг
- `.htaccess` → `/bitrix/urlrewrite.php`
- `system/server/data/bitrix/.settings.php` читает `app/.env`
- `local/php_interface/init.php` — автозагрузка и события

### Каталог (ключевое)
- URL: категория / подвид / модель; множественный выбор через `-i-`
- Smart‑filter: AJAX, канонический порядок сегментов

## Обзор проекта (Bitrix, Trimiata)

### Стек и роль каталогов
- **PHP/Bitrix (D7)**: бизнес-логика, события, интеграции в `app/local/php_interface/lib/...` и компоненты в `app/local/components/*`.
- **Докрутка ядра Bitrix**: ядро монтируется в `app/bitrix` (см. docker compose). Реальные конфиги Bitrix в `system/server/data/bitrix/.settings.php` (пробрасываются в контейнер как `app/bitrix/.settings.php`).
- **Докруты шаблона**: `app/local/templates/trimiata` (стили/скрипты и шаблоны компонентов). Исходники JS/SCSS находятся в `app/local/changes/template/src/` и компилируются через webpack в `app/local/templates/trimiata/dist/` и `bundle/`.
- **Cron/скрипты обслуживания**: `app/local/cron/*`.
- **API-витрина**: `app/api/*` (e.g. `webhook/*`, `order/*`, `product/*`, `yml/*`, `xml/*`).
- **Инфраструктура**: `system/server/compose.yaml`, Dockerfile'ы в `system/lib/{php,nginx}`; Makefile в корне для запуска.

### Точки входа (web)
- `app/.htaccess`: mod_rewrite на `/bitrix/urlrewrite.php` (Bitrix фронт-контроллер).
- `app/bitrix/urlrewrite.php`: отдаёт 404 при отсутствии маршрута; основные ЧПУ-правила в `app/urlrewrite.php` (каталог, новости, API-ручки и пр.).
- `app/index.php`: главная с набором собственных компонентов `app:*` (секции каталога, баннеры и т.д.).

### Конфигурация Bitrix и окружения
- Базовый конфиг Bitrix: `system/server/data/bitrix/.settings.php`
  - Загружает `.env` из `app/.env` (через `vlucas/phpdotenv`).
  - Требует `DB_HOST, DB_NAME, DB_LOGIN, DB_PASSWORD` (assert).
  - Опции: `DEBUG`, `IS_DEV` (булевы), `MEMCACHED_HOST` (каширующая подсистема), Redis-сессии вне dev.
- Глобальная инициализация: `app/local/php_interface/init.php`
  - Подключает автозагрузку и инклюды: `lib/app/inc.php`, `lib/entity/inc.php`, `lib/events/inc.php`.
  - `lib/events/*` регистрирует хендлеры D7 через `EventManager` (main/sale/catalog/...); см. `events/main/BeforeProlog.php`, `events/sale/Order.php`.
- Константы проекта: `app/local/php_interface/inc/constants.php` (ID инфоблоков, валюты, прайс-группы, интеграционные ключи из ENV).

### Структура каталога (верхний уровень)
- `app/` — DocumentRoot проекта (Bitrix внутри `app/bitrix`).
- `docs/` — проектная документация (этот файл и другие артефакты).
- `system/` — инфраструктура (Docker, Nginx, PHP-FPM, compose). 
- `Makefile` — обёртки для docker compose (init/start/stop/import).

### Ключевые директории внутри `app/`
- `bitrix/` — ядро Bitrix (монтируется извне; `.settings.php` пробрасывается из `system/server/data/bitrix/.settings.php`).
- `local/components/*` — кастомные компоненты `app:*`, `opensource:*`, `trimiata:*`, `koptelnya:*`.
- `local/modules/opensource.order` — установленный модуль заказа (включает свои компоненты и либы).
- `local/php_interface/` — конфиги, инит и библиотека доменного кода.
- `local/templates/trimiata/` — боевой шаблон сайта (JS/CSS сборки, include areas, bundle/dist).
- `local/cron/*` — задачи крон (импорт, обмен с 1С, seo, заказы и т.д.).
- `api/*` — REST/HTTP-витрина (вебхуки ImShop, 1С, подсказки, корзина, оплата, выгрузки XML/YML).

### Поведение запроса (высокоуровнево)
1) Nginx → PHP-FPM → `app/.htaccess` → `/bitrix/urlrewrite.php`.
2) Bitrix загружает `app/bitrix/.settings.php` (из volume), инициализирует окружение.
3) `local/php_interface/init.php` подключает автолоад и регистрирует события.
4) D7 Events (`main`, `sale`, `catalog` и пр.) исполняют прикладную логику (редиректы/SEO/UTM, ecommerce dataLayer, пост-обработка заказов, интеграции).

### Бизнес-области
- Каталог (инфоблоки, фильтры, собственные компоненты каталога и карточки товара).
- Оформление/заказы (компоненты, события `sale:*`, интеграции с платежами и доставками, письма).
- Интеграции: 1С (обмен/стек), ImShop (мобильное приложение), DaData (адреса), Яндекс (Webmaster/SmartCaptcha), DPD/EMS (доставка).

### Что дальше
См. подробности в следующих файлах:
- `architecture-map.md` — архитектура и потоки.
- `modules-and-components.md` — инвентарь компонентов/модулей.
- `data-model.md` — IBLOCK/HL-блоки, маппинги и константы.
- `integrations.md` — внешние сервисы, точки входа.
- `build-and-ops.md` — сборки, окружения, Makefile/Compose, фронт.
- `security-and-quality.md` — риски и быстрые улучшения.
- `runbook.md` — запуск локально, сценарии.

### Правила для разработчиков (важно)
- Подключение классов (use): один блок, пути с начальным слешем; порядок — Bitrix → внешние → App.
- Комментарии в коде — только на русском, лаконично и понятно: описываем цель и причину изменений.




