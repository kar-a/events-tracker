## Сборка и эксплуатация — **events.trimiata.ru** (этот репозиторий)

- Код и compose: `system/events-service/`.
- Создать `system/events-service/infra/compose/.env` из `.env.example`, затем `make up` из каталога `system/events-service` (см. [system/events-service/README.md](../system/events-service/README.md)).
- Сборка контракта: `make build-contract` / тесты: `make test-contract`.
- Контур событий **независим** от PHP-FPM и каталога `app/`.

---

## Справочно: основной сайт **trimiata.ru** (Bitrix, другой клон)

Ниже — типовая схема полного монорепозитория с сайтом. **В проекте только events эти пути могут отсутствовать.**

### Docker/Compose
- Конфигурация: `system/server/compose.yaml`.
  - Volumes:
    - `../../app:/var/www/app`
    - `${BITRIX_CORE_PATH}:/var/www/app/bitrix`
    - `${UPLOAD_FOLDER_PATH}:/var/www/app/upload`
    - `data/bitrix/.settings.php -> /var/www/app/bitrix/.settings.php`
  - Сервисы: `php` (PHP-FPM), `nginx` (dev, 8080:80, 8443:443), сети внешний `nginx_network/mysql_network/memcached_network`.
- Makefile команды:
  - `make init|start|startd|stop|remove|restart`
  - `make import` — пример выполнения cron в контейнере.

### ENV
- `.env` ожидается в `app/.env` (см. `system/server/README.md`).
- Критичные переменные (см. `system/server/data/bitrix/.settings.php`):
  - `DB_HOST, DB_NAME, DB_LOGIN, DB_PASSWORD` — БД
  - `DEBUG, IS_DEV` — флаги
  - `MEMCACHED_HOST` — кеш
  - Пул интеграционных ключей в `app/local/php_interface/inc/constants.php`.

### PHP-пакеты
- `app/local/php_interface/lib/composer.json`:
  - PHP>=8.1, symfony 6.1, monolog, dotenv, geoip2, mobiledetect, jwt и др.
  - ВНИМАНИЕ: содержит `config.github-oauth` — убедиться, что секрет не попадает в публичный VCS.

### Проверки качества/безопасности
- Статические правила:
  - Запрещаем попадание `.env`, `upload/`, `bitrix/` ядра — в VCS.
  - Проверяем отсутствие OAuth‑токенов в `composer.json`.
- Lint/проверки перед PR:
  - Быстрый прогон на 404/редиректы — `events/main/BeforeProlog.php` стабильно выполняет нормализацию.
  - Консистентность ЧПУ каталога — `Catalog\Helper::checkUriOrder()`.

### Фронтенд
- Исходники/скрипты сборки: файл `app/local/changes/template/package.json` (webpack 5).
- Структура JS исходников:
  - `app/local/changes/template/src/js/App.js` — главный модуль приложения
  - `app/local/changes/template/src/js/AppApi.js` — утилиты для API запросов
  - `app/local/changes/template/src/js/AppStorage.js` — утилиты для работы с хранилищем
  - `app/local/changes/template/src/js/app/` — модули функциональности (`AppBasket.js`, `AppForm.js`, `AppSmartFilter.js`, `AppOrder.js` и т.д.)
  - `app/local/changes/template/src/js/modules/` — вспомогательные модули (`Scrollyeah.js` и т.д.)
  - `app/local/changes/template/src/js/Router.js` — роутер для lazy loading модулей
  - `app/local/changes/template/src/js/scripts.js` — агрегатор модулей UI
- Билд: webpack компилирует JS из `src/js/` в `app/local/changes/template/dist/js/`, затем afterbuild копирует в `app/local/templates/trimiata/dist/js/` и `bundle/js/`.
- Скрипты: `npm run build`, `npm run figma` (WSL bash hook).


### Фронтенд — правила сборки (без sh) [Task 1150]
- **Команды**: выполняйте последовательно `npm run build` и затем `npm run postbuild`.
  - **build**: `webpack --config webpack.bx_build.js` — собирает ассеты в `app/local/changes/template/dist`.
  - **postbuild**: `node webpack.afterbuild.js` — кроссплатформенный afterbuild (без bash/WSL).
- **Действия afterbuild**:
  - Удаляет `app/local/templates/trimiata/bundle` (рекурсивно, безопасно).
  - Копирует содержимое `dist` → `app/local/templates/trimiata/bundle`.
  - Дополнительно копирует `src/img` → `app/local/templates/trimiata/bundle/img` (если каталог существует).
- **Причина/эффект**:
  - Ранее использовались shell‑скрипты (WSL), что ломало сборку на Windows/в CI.
  - Перевод postbuild на Node устраняет зависимость от `sh`, сборка становится одинаковой на всех ОС.
- **Правила**:
  - Никаких интерактивных шагов; скрипты обязаны завершаться кодом ошибки при сбоях (напр., если `dist` отсутствует).
  - Любые копирования/удаления — через Node API `fs/promises` (без spawn/exec).
  - Путь назначения всегда `app/local/templates/trimiata/bundle` (единый источник правды для шаблона).
  - Допустимы предупреждения webpack/autoprefixer; ошибки — блокируют пайплайн.
- **CI**:
  - Используйте связку: `npm ci && npm run build && npm run postbuild`.
  - Кэшируйте `~/.npm` в пайплайне; артефактом сохраняйте `app/local/templates/trimiata/bundle`.

