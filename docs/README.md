# Trimiata

## Изменения (Changelog)
См. журнал изменений: [docs/CHANGELOG.md](./CHANGELOG.md)

## Содержание
* [Необходимые зависимости](#dependencies)
* [Code style](#codestyle)
* [Правила редактирования кода](#code-editing-rules)
* [Git и GitHub](./git-and-github.md)
* [План онбординга и контекста](./onboarding-and-context.md)
* [Память задач (AI Memory)](./memory/INDEX.md)
* [Локальное окружение](#localenv)
* [Стейдж окружения (тонкое)](#stageenv)
* [Продакшн окружение](#prodenv)
* [Работа с базой данных](#db)
* [Gitflow](#gitflow)
* [Сборка фронта](#front-bulder)
* [Установка пакетов PHP](#php-packages)
* [Подключение memcached](#turn-on-memcached)
* [Проверка\замены прав, владельца, группы у файлов и каталогов](#report-permissions)

## <a name="dependencies"></a>Необходимые зависимости
1) [Docker](https://docs.docker.com/install/)
2) [Docker-Compose](https://docs.docker.com/compose/install/)
3) [Make](https://www.gnu.org/software/make/)
4) [Composer](https://getcomposer.org/doc/00-intro.md)

## <a name="codestyle"></a>Code style
Важно придерживаться принятых соглашений по оформлению кода.  
Ознакомиться можно в .editorconfig.  
А так же убедиться, что IDE использует настройки из .editorconfig.

## <a name="code-editing-rules"></a>Правила редактирования кода
См. `docs/code-editing-rules.md` — краткие договоренности по edits и сборке фронта.

## Документация
- [Архитектура и потоки](architecture-map.md) — компоненты системы, жизненный цикл запроса, потоки данных
- [Сборка и эксплуатация](build-and-ops.md) — Docker/Compose, ENV, PHP-пакеты, фронтенд
- [Модель данных](data-model.md) — IBLOCK/HL‑схемы, индексы, URL‑паттерны, кеш
- [Интеграции](integrations.md) — внешние сервисы, API, вебхуки
- [Модули и компоненты](modules-and-components.md) — Bitrix компоненты, шаблоны, JS-модули
- [Карта знаний](knowledge-map.md) — быстрый справочник по ключевым зонам
- [Ранбук](runbook.md) — шаги запуска dev, сборка фронта, QA‑чеклист
- [Безопасность и качество](security-and-quality.md) — правила, проверки, линты
- [Система лоадеров и динамический контент](frontend-loaders.md) — API лоадеров, замена контента, мобильная специфика
- [События и хуки](events-and-hooks.md) — система событий Bitrix D7, регистрация, обработка

## <a name="localenv"></a>Локальное окружение
#### Для локальной разработки используется полностью контейниризованное окружение.
* php
* mysql
* phpmyadmin
* nginx

```bash
    make localup
    make localupd
    make localdown
```
#### Помимо инфраструктуры понадобятся следующие компоненты которые нужно взять на проде:
* ядро
* папка upload 
* актуальная копия базы данных

#### Пример запуска локального окружения:
1. Склонировать репозиторий
2. Скачать папку `upload`, положить в `app/`
3. Скачать папку `app/bitrix`, положить в `app/`
4. Запустить проект `make localup`
5. Открыть в браузере phpmyadmin http://127.0.0.1:1000
6. Сделать выгрузку дампа на проде и залить в локальную базу данных

## <a name="stageenv"></a>Стейдж окружения (тонкое)
Следующий этап разработки после локального окружения производится на удаленной виртуальной машине.
* php
* nginx

Независимо (shared) на стейдж машине разворачиваются:
* mysql & phpmyadmin
* reverse proxy nginx (в конфиге настраивается маппинг по доменному имени на соответствующий экземпляр стейдж окружения)
* ядро
* папка upload
* memcached

Перечисленные ресурсы могут быть одновременно использованы несколькими экземплярами стейдж окружения. Ресурсы разворачиваются отдельно, после их подготовки запускаем экземпляр стейдж окружения.

```bash
make thinup
make thinupd
make thindown
```

### Переменные окружения
Для тонкого окружения используется 2 файла .env:
* Первый содержит переменные окружения, которые используются в docker-compose файле. [Образец.](infra/thin/.env.example)
  * Используется для указания пути к ядру и папке upload, а также для указания имени докер проекта. 
* Второй содержит переменные окружения, которые используются в приложении. [Образец.](app/.env.example)
  * Настройки подключения к бд, memcached, серверу фотографий и включение режима отладки. 
* Для каждого экземпляра стейдж окружения создается свой файл .env.
* Размещается боевой .env файл в той же директории, что и example файл.

## <a name="prodenv"></a>Продакшн окружение
// TO DO  
На текущий момент прод поддерживается силами хостера.

### Переменные окружения из .env
* Установить зависимости `composer install`
  * composer.json хранится в /app/local/composer.json (main) & /app/local/php_interface/lib/composer.json (develop)
* Разместить .env файл в /app/.env

## <a name="db"></a>Работа с базой данных
Типовые операции это экспорт и импорт.
Удобнее работать через phpmyadmin, но при возникновении ошибок cli вариант зарекомендовал себя как более надежный способ.
### phpmyadmin
Логин пароль для входа можно посмотреть в `docker-compose.yaml` соответствующего окружения.
После импорта, возможно нужно будет дать доп права. Для этого идём home -> User accounts. Выбрать нужного пользователя -> Database.
Так же проверка прав помогает при некоторых ошибках бд.
### cli
#### credentials
В `isp panel` => `databases` => `database servers` => выбираем нужную СУБД => меню (троеточие) => `edit` => видно root пароль
#### Экспорт
```bash
# mysql как сервис
mysql -u username -p database_name > my_unzipped_import_file.sql

# mysql в контейнере
mysql -h 127.0.0.1 -P 3310 -u user_name -p database_name > my_unzipped_import_file.sql
```
#### Импорт
```bash
# mysql как сервис
mysql -u username -p database_name < my_unzipped_import_file.sql

# mysql в контейнере
mysql -h 127.0.0.1 -P 3310 -u user_name -p database_name > my_unzipped_import_file.sql
```

## <a name="gitflow"></a>Gitflow
При использовании гита придерживаемся следующей стратегии.
* .gitignore в корне проекта > куча .gitignore файлов в разных директориях проекта.
* Последний коммит ветки main должен содержать версию идентичную состоянию файлов на проде.
* Ветка main всегда актуальна и готова к деплою.
* В main код поступает только через pull request.
* В main код поступает только через 2 направления: release и hotfix.
  * develop - ветка для разработки новых фич. Перед началом очередного цикла актуализируем её с main. После завершения цикла сливаем в main.
  * hotfix - ветка для исправления критических ошибок на проде. Отпучковываем от main. После готовности сливаем в main и main в develop.

## Политика ведения Changelog
- Формат: Keep a Changelog + SemVer (MAJOR.MINOR.PATCH).
- После каждой завершённой задачи обновляй раздел Unreleased в [docs/CHANGELOG.md](./CHANGELOG.md): кратко Что (Added/Changed/Removed/Fixed/Security), Зачем/Impact, ссылки на затронутые файлы и (при наличии) PR/коммиты.
- Для крупных тем добавляй подпункт «Files → Effects → Intent» и короткий QA‑чеклист.
- Один PR/фича → один логический блок записей; группируй по секциям.
- При релизе переноси записи из Unreleased в новый раздел с версией и датой.

### Commit summary для CI
- После завершения работы reviewer_agent формируй краткое описание коммита в формате «Files → Effects → Intent (и при необходимости QA/Checks)» и сохраняй его в файле `.github/commit-summary.txt`.
- GitHub Action `.github/workflows/stage-pull-on-commit.yaml` использует этот файл для отправки информативного уведомления в Telegram (fallback — извлечение Effects/Intent из `docs/CHANGELOG.md`).
  - Стиль: простой, понятный, информационный. Без жаргона и лишних деталей. 2 коротких абзаца с маркерами: `Effects →` и `Intent →`.
  - Формат: жирно выделяются маркеры в уведомлении, пустая строка между блоками (оформление делает CI).
  - Источник для CI: сначала `.github/commit-summary.txt`, при отсутствии — строки `Effects →/Intent →` из `docs/CHANGELOG.md` (или первые строки блока Added).
  - Назначение: текст показывается пользователям в Telegram‑уведомлении о деплое.
  - Доп. правило: если в `docs/CHANGELOG.md` для записи нет ссылок на коммиты, reviewer_agent обязан найти и добавить короткие хэши коммитов в конец строки `Intent →` (формат: `commits: \`abc123\`, \`def456\``).

### Definition of Done (обязательно)
- После завершения задачи добавь запись в раздел Unreleased `docs/CHANGELOG.md`.
- Укажи: краткое действие (императив), Why/Impact (1–2 строки), ссылки на файлы (`app/...`, `docs/...`), при наличии — PR/issue/коммиты.
- Для изменений URL/фильтра дополнительно обнови `docs/architecture-map.md`, `docs/knowledge-map.md`, `docs/data-model.md`.
- При релизе: перенеси Unreleased в новый раздел версии (SemVer) с датой и создай тег релиза в Git.

### Стиль записей
- Кратко, в повелительном наклонении: «Добавь», «Обнови», «Исправь», «Удали».
- Структура: заголовок пункта → (для крупных тем) подпункты `Files → Effects → Intent`.
- Всегда пиши Why/Impact (чем это полезно пользователю/SEO/UX/перфомансу).
- Ссылки: относительные пути к файлам; допускаются хэши коммитов/PR.
- Не публикуй секреты и приватные ссылки.

## <a name="front-bulder"></a>Сборка фронта
* Для сборки фронта требуется `src` файлы превратить в файлы `dist` через webpack.
* Путь к `src` файлам: `app/local/changes/template/src/` (SCSS в `src/styles/`, JS в `src/js/`).
* Путь к `dist` файлам: `app/local/templates/trimiata/dist/` (CSS в `dist/css/`, JS в `dist/js/`).
* Структура JS исходников:
  - `app/local/changes/template/src/js/App.js` — главный модуль
  - `app/local/changes/template/src/js/AppApi.js` — утилиты API
  - `app/local/changes/template/src/js/app/` — модули функциональности (`AppBasket.js`, `AppForm.js`, `AppSmartFilter.js` и т.д.)
  - `app/local/changes/template/src/js/modules/` — вспомогательные модули
  - `app/local/changes/template/src/js/Router.js` — роутер для lazy loading
* Для запуска полной сборки (~ 2 мин) требуется выполнить команду `make build_front`
* Для запуска сборки только css (~ 30 сек.) требуется выполнить команду `make build_css`
* Для запуска сборки только js (~ 60 сек.) требуется выполнить команду `make build_js`
* **Важно**: не редактировать файлы в `dist/` напрямую — они генерируются автоматически при сборке. Все правки делаются в `src/`.

## <a name="php-packages"></a>Установка пакетов PHP
* Для установки пакетов PHP используем composer.
* В гите не храним папку vendor, а только composer.json.
* Для установки пакетов используем докер.
* Для запуска сборки пакетов требуется выполнить команду `make composer_install`, будет запущен контейнер и результат работы будет помещен в папку vendor.

## <a name="turn-on-memcached"></a>Подключение memcached
* Для thin окружения используем несколько экземпляров приложения.
* Для каждого приложения нужно использовать свой экземпляр memcached.
* На stage запущено 3 контейнера memcached.
* Для указания конкретному окружению конкретного контейнера memcached используем .env file, переменная `MEMCACHED_HOST`, в которую пишем имя контейнера, которое выступает доменным именем.
* Даем имя контейнерам по схожей маске как это делает docker-compose, например: `dev_trimiata_ru-memcached-1`.

## <a name="report_permissions"></a>Проверка\замены прав, владельца, группы у файлов и каталогов
* Команда `make report_permissions` запускает скрипт
* [Документация на скрипт](infra/report_permissions/README.MD)
