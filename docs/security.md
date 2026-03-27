# Политика безопасности проекта Trimiata

Документ описывает подходы, правила и практики обеспечения безопасности сайта, кода и защиты от атак.

**Версия:** 2.0  
**Дата обновления:** 2026-01-27  
**Ответственный:** Команда разработки

**Контур events.trimiata.ru (кратко):** публичный приём только по **HTTPS**; тела событий валидировать по контракту; не писать в логи полные payload с персональными данными; секреты — только из окружения / secret store; **ClickHouse** и **Redis** не выставлять в интернет без TLS и ACL; на коллекторе — rate limiting и защита от злоупотреблений. Подробнее — [architecture-map.md](./architecture-map.md), [system/events-service/docs/deployment.md](../system/events-service/docs/deployment.md).

---

## Содержание

1. [Общие принципы безопасности](#общие-принципы-безопасности)
2. [Защита от сканеров уязвимостей](#защита-от-сканеров-уязвимостей)
3. [Защита от XSS (Cross-Site Scripting)](#защита-от-xss-cross-site-scripting)
4. [Защита от SQL Injection](#защита-от-sql-injection)
5. [Защита от CSRF (Cross-Site Request Forgery)](#защита-от-csrf-cross-site-request-forgery)
6. [Защита от Open Redirect](#защита-от-open-redirect)
7. [Контроль доступа (Access Control)](#контроль-доступа-access-control)
8. [Работа с секретами и конфигурацией](#работа-с-секретами-и-конфигурацией)
9. [Аутентификация и авторизация](#аутентификация-и-авторизация)
10. [Обработка пользовательского ввода](#обработка-пользовательского-ввода)
11. [Валидация параметров запроса](#валидация-параметров-запроса)
12. [Защита от загрузки файлов](#защита-от-загрузки-файлов)
13. [Защита от DoS атак](#защита-от-dos-атак)
14. [JavaScript безопасность](#javascript-безопасность)
15. [Логирование и мониторинг](#логирование-и-мониторинг)
16. [Защита API эндпоинтов](#защита-api-эндпоинтов)
17. [Отладочный код в проде](#отладочный-код-в-проде)
18. [Инфраструктурная безопасность](#инфраструктурная-безопасность)
19. [Best Practices для разработчиков](#best-practices-для-разработчиков)

---

## Общие принципы безопасности

### Принцип наименьших привилегий
- Пользователи и процессы должны иметь минимально необходимые права доступа
- Административные функции доступны только авторизованным администраторам
- API эндпоинты проверяют права доступа перед выполнением операций

### Defense in Depth (Многоуровневая защита)
- Защита на уровне приложения, веб-сервера, сети и инфраструктуры
- Не полагаться на один механизм защиты
- Регулярный аудит безопасности

### Не доверять пользовательскому вводу
- Все данные от пользователя считаются потенциально опасными
- Валидация и санитизация на входе
- Экранирование при выводе

### Принцип явности
- Явная обработка ошибок и исключений
- Явная проверка прав доступа
- Явное логирование критичных операций

---

## Защита от сканеров уязвимостей

### Механизм блокировки

**Файл:** `app/local/php_interface/lib/events/main/BeforeProlog.php`  
**Метод:** `checkScanners()`  
**Событие:** `main:OnBeforeProlog` (приоритет 0)

### Принцип работы

1. Проверка выполняется до инициализации контента страницы
2. Игнорируются хорошие боты (`isGoodBot()`)
3. Игнорируются авторизованные администраторы
4. При обнаружении сканера:
   - Блокируется IP через `CSecurityIPRule` на 30 дней
   - Логируется попытка в `_blockScanners_*`
   - Возвращается статус 403 Forbidden

### Типы проверок

#### 1. `requestStarts` — проверка начала URL
Проверяет, начинается ли URL с указанного паттерна. Безопасно для корневых путей.

**Примеры:**
- `/bitrix/restore.php` — блокирует известную уязвимость CVE-2022-29268
- `/bitrix/admin/bitrix.xscan` — блокирует CVE-2015-8357
- `/phpinfo.php` — блокирует информационные файлы
- `/wp-config.php` — блокирует конфигурационные файлы WordPress

**Конфигурация:** `app/local/php_interface/config/blocker.php` → `scanChecking.requestStarts`

#### 2. `requestIncludes` — проверка наличия подстроки
Проверяет наличие подстроки в URL. **Осторожно:** может блокировать легитимные URL.

**Правила:**
- Использовать только специфичные паттерны (shell-скрипты, известные векторы атак)
- Избегать коротких паттернов типа `'1/'`, `'/p/'`, `'/c/'` — они блокируют легитимные URL
- Проверять конфликты с реальными URL через sitemap.xml

**Примеры безопасных паттернов:**
- `'xleet'`, `'c99'`, `'wso'` — известные shell-скрипты
- `'wp-config.php.bak'` — варианты бэкапов конфигов
- `'.env'` — файлы окружения
- `'backup.sql'` — бэкапы БД

**Примеры конфликтующих паттернов (удалены):**
- `'1/'`, `'2/'` — блокировали детальные страницы `/catalog/product-name-12345678-1/` и пагинацию `/catalog/.../page-1/`
- `'/p/'`, `'/c/'` — блокировали `/catalog/product-name/` и `/catalog/category/`
- `'catalog45'`, `'catalog10'` — конфликт с реальными URL каталога

**Конфигурация:** `app/local/php_interface/config/blocker.php` → `scanChecking.requestIncludes`

#### 3. `noReferer` — проверка запросов без реферера
Блокирует запросы к критичным путям без HTTP_REFERER (подозрение на прямой доступ сканером).

**Примеры:**
- `/phpinfo.php` — информационные файлы
- `/admin.php`, `/administrator` — админ-панели
- `/bitrix/admin` — админка Bitrix
- `/.env`, `/backup.sql` — секреты и бэкапы

**Конфигурация:** `app/local/php_interface/config/blocker.php` → `scanChecking.noReferer`

#### 4. User-Agent проверка
Блокировка по User-Agent через таблицу `SeoAutoblockTable` (тип `SCANNERS_TYPE_USER_AGENT`).

### Расширение списков блокировки

**Правила добавления паттернов:**

1. **Для `requestStarts`:**
   - Добавлять только корневые пути (начинающиеся с `/`)
   - Проверять, что путь не конфликтует с реальными URL сайта
   - Примеры: `/bitrix/restore.php`, `/phpinfo.php`, `/wp-config.php`

2. **Для `requestIncludes`:**
   - Использовать только специфичные паттерны (shell-скрипты, известные векторы)
   - Избегать коротких паттернов (1-2 символа)
   - Проверять конфликты через sitemap.xml и структуру URL каталога
   - Примеры: `'xleet'`, `'c99'`, `'wp-config.php.bak'`, `'.env'`

3. **Для `noReferer`:**
   - Добавлять только критичные пути без реферера
   - Примеры: `/phpinfo.php`, `/admin.php`, `/bitrix/admin`, `/.env`

4. **Проверка конфликтов:**
   - Изучить sitemap.xml на наличие похожих URL
   - Проверить структуру URL каталога (`/catalog/{category}/{product-name-{8digits}(-1)?}/`)
   - Проверить пагинацию (`/catalog/.../page-{N}/`)
   - Удалить конфликтующие паттерны

**Файл конфигурации:** `app/local/php_interface/config/blocker.php`

### Мониторинг и логирование

- Логи блокировок: `_blockScanners_requestStarts`, `_blockScanners_requestIncludes`, `_blockScanners_noReferer`
- Периодически проверять логи на ложные срабатывания
- При обнаружении ложных срабатываний — удалить или уточнить паттерн

---

## Защита от XSS (Cross-Site Scripting)

### Правила экранирования вывода

#### В PHP шаблонах
```php
// ✅ Правильно: экранирование HTML
<?= htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8') ?>

// ✅ Правильно: для атрибутов
<a href="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>">

// ❌ Неправильно: прямой вывод без экранирования
<?= $userInput ?>
```

#### В JavaScript
```javascript
// ✅ Правильно: использование textContent вместо innerHTML
element.textContent = userInput;

// ✅ Правильно: экранирование при вставке HTML
const escaped = userInput.replace(/&/g, '&amp;')
                         .replace(/</g, '&lt;')
                         .replace(/>/g, '&gt;')
                         .replace(/"/g, '&quot;')
                         .replace(/'/g, '&#039;');
```

#### В JSON
```php
// ✅ Правильно: использование JSON_UNESCAPED_UNICODE для корректной кодировки
json_encode($data, JSON_UNESCAPED_UNICODE);

// ✅ Правильно: экранирование в HTML-атрибутах с JSON
<div data-json="<?= htmlspecialchars(json_encode($data), ENT_QUOTES, 'UTF-8') ?>">
```

### Примеры из проекта

**Файл:** `app/ordererror/index.php`
```php
<? if ($orderId = Ctx::request()->get('order_id')) { ?>
    <b>Заказ №<?= htmlspecialchars($orderId, ENT_QUOTES, 'UTF-8') ?></b>
<? } ?>
```

**Файл:** `app/local/php_interface/lib/events/yandexmarket/onExportOfferWriteData.php`
```php
$tagNode->addChild('param', \htmlspecialchars($insertWeightSumToAdd));
```

### Content Security Policy (CSP)

**Файл:** `app/local/php_interface/lib/app/Template/Helper.php`
```php
Asset::getInstance()->addString(
    '<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests" />',
    true,
    AssetLocation::BEFORE_CSS
);
```

**Рекомендации:**
- Расширить CSP для блокировки inline scripts и styles
- Использовать nonce для разрешенных inline скриптов
- Ограничить источники загрузки ресурсов

---

## Защита от SQL Injection

### Использование ORM Bitrix D7

**✅ Правильно:**
```php
use Bitrix\Main\ORM\Query\Query;

$result = ModelTable::getList([
    'filter' => [
        '=UF_XML_ID' => $xmlId,
        '=UF_ACTIVE' => true
    ],
    'select' => ['ID', 'UF_NAME']
]);
```

### Использование подготовленных запросов

**✅ Правильно:**
```php
$connection = \Bitrix\Main\Application::getConnection();
$sqlHelper = $connection->getSqlHelper();

$sql = "SELECT * FROM table WHERE id = " . intval($id);
// или
$sql = "SELECT * FROM table WHERE name = '" . $sqlHelper->forSql($name) . "'";
```

**❌ Неправильно:**
```php
// Прямая подстановка без экранирования
$sql = "SELECT * FROM table WHERE name = '$name'";
```

### Валидация типов данных

```php
// ✅ Правильно: приведение к типу
$id = (int)$_GET['id'];
$userId = (int)($ar['user_fields']['ID'] ?? 0);

// ✅ Правильно: проверка перед использованием
if ($id <= 0) return;
```

---

## Защита от CSRF (Cross-Site Request Forgery)

### Использование Bitrix sessid

**В формах:**
```php
<input type="hidden" name="sessid" value="<?= bitrix_sessid() ?>">
```

**В AJAX запросах:**
```javascript
// ✅ Правильно: передача sessid в запросе
api.request('/api/endpoint', 'POST', 'json', {
    sessid: BX.bitrix_sessid(),
    // ... другие данные
});

// ✅ Правильно: передача в заголовке
xhr.setRequestHeader('X-Bitrix-Csrf-Token', _component._sessid);
```

**Проверка в API:**
```php
// Файл: app/api/core.php
if ($checkSessid && (!$isAjaxRequest || !\check_bitrix_sessid() || Router::getInstance()->isBot())) {
    $this->addError(new Error('Сессия истекла, пожалуйста, обновите страницу'));
    $this->setData(['sessid' => \bitrix_sessid()]);
    $this->status = 403;
}
```

### Исключения из проверки CSRF

**Файл:** `app/api/core.php`

Исключения для публичных эндпоинтов (только для чтения):
- `/api/1c/` — интеграция с 1С
- `/api/basket/` — корзина (публичные операции)
- `/api/location/` — определение местоположения
- `/api/modal/` — модальные окна
- `/api/news/` — новости
- `/api/product/` — информация о товарах
- `/api/user/` — публичные данные пользователя

**Важно:** Критичные операции (создание заказа, изменение данных) должны проверять CSRF.

---

## Защита от Open Redirect

### Проблема
Open Redirect уязвимость позволяет злоумышленнику перенаправить пользователя на произвольный внешний сайт через параметр `backurl` или аналогичный.

### Правила валидации URL

**✅ Правильно:**
```php
// Файл: app/auth/index.php
if ($backUrl = Ctx::request()->get('backurl')) {
    // Валидация URL для предотвращения Open Redirect атаки
    $parsedUrl = parse_url($backUrl);
    $currentHost = Ctx::request()->getHttpHost();

    // Разрешаем только относительные URL или URL с тем же доменом
    if (!$parsedUrl || (!isset($parsedUrl['host']) || $parsedUrl['host'] === $currentHost)) {
        // Проверяем, что путь начинается с / (относительный путь)
        if (strpos($backUrl, '/') === 0 || !isset($parsedUrl['scheme'])) {
            \LocalRedirect($backUrl);
        } else {
            \LocalRedirect(SITE_DIR . 'catalog/');
        }
    } else {
        \LocalRedirect(SITE_DIR . 'catalog/');
    }
} else {
    \LocalRedirect(SITE_DIR . 'catalog/');
}
```

**Правила:**
- Всегда валидировать URL перед редиректом через `parse_url()`
- Разрешать только относительные URL (начинающиеся с `/`) или URL с тем же доменом
- Запрещать внешние домены в параметрах редиректа
- Использовать белый список разрешенных путей, если возможно
- По умолчанию редиректить на безопасную страницу при невалидном URL

**❌ Неправильно:**
```php
// Прямой редирект без валидации
LocalRedirect($_GET['backurl']);

// Разрешение внешних доменов
if (filter_var($url, FILTER_VALIDATE_URL)) {
    LocalRedirect($url); // ❌ Опасно!
}
```

---

## Контроль доступа (Access Control)

### Проверка владельца ресурса

**Проблема:** Пользователи могут получать доступ к ресурсам других пользователей (например, заказы, профили).

**✅ Правильно:**
```php
// Файл: app/api/order/index.php
// Проверка владельца заказа для критичных операций
if ($order && in_array($action, ['changePaySystem', 'cancelOrder'])) {
    $orderUserId = (int)$order->getField('USER_ID');
    $userId = $USER->IsAuthorized() ? (int)$USER->GetID() : \CSaleUser::GetAnonymousUserID();
    
    if (!$userId || $orderUserId !== $userId) {
        $result->addError(new Error('Ошибка доступа'));
        $result->sendJsonResponse();
        exit;
    }
}
```

**Правила:**
- Всегда проверять владельца ресурса перед изменением/удалением
- Использовать явное приведение типов `(int)` для ID
- Проверять авторизацию перед проверкой владельца
- Для анонимных пользователей использовать `CSaleUser::GetAnonymousUserID()`
- Возвращать общую ошибку "Ошибка доступа" без деталей (не раскрывать существование ресурса)

### Проверка прав администратора

```php
// ✅ Правильно: проверка администратора
global $USER;
if ($USER->IsAuthorized() && $USER->IsAdmin()) {
    // Административные операции
} else {
    // Ошибка доступа
}
```

### Проверка сессии для критичных операций

```php
// ✅ Правильно: проверка сессии для отмены заказа
if ($action === 'cancelOrder') {
    if (!isset($_SESSION['SALE_ORDER_ID']) || !is_array($_SESSION['SALE_ORDER_ID']) || !in_array($id, $_SESSION['SALE_ORDER_ID'])) {
        $result->addError(new Error('Ошибка доступа'));
        $result->sendJsonResponse();
        exit;
    }
}
```

---

## Работа с секретами и конфигурацией

### Хранение секретов

**✅ Правильно:**
- Все секреты в переменных окружения (`.env`)
- `.env` исключен из VCS (`.gitignore`)
- `.env.example` содержит шаблон без реальных значений
- Секреты загружаются через `constants.php` из `$_ENV`

**❌ Неправильно:**
- Хардкод секретов в коде
- Коммит `.env` в репозиторий
- Секреты в конфигурационных файлах, которые попадают в VCS

### Примеры из проекта

**Файл:** `app/local/php_interface/inc/constants.php`
```php
// ✅ Правильно: загрузка из окружения
define('APP_OPENAI_API_KEY', $_ENV['OPENAI_API_KEY'] ?? '');
define('APP_PASSWORD_1C', $_ENV['PASSWORD_1C']);
define('APP_DADATA_API_KEY', $_ENV['DADATA_API_KEY'] ?? '');
define('APP_DADATA_SECRET', $_ENV['DADATA_SECRET'] ?? '');
```

**Файл:** `app/local/php_interface/lib/app/Seo/Content/OpenAi/OpenAi.php`
```php
// ✅ Правильно: использование константы из окружения
$this->apiKey = \App\Ctx::config()->get('openai.api_key') 
    ?: (defined('APP_OPENAI_API_KEY') ? APP_OPENAI_API_KEY : '');
```

### Защита конфигурационных файлов

**Блокировка доступа к конфигам:**
- `/bitrix/.settings.php` — в списке блокировки сканеров
- `/wp-config.php` и варианты бэкапов — в списке блокировки
- `/.env` — в списке блокировки
- Конфиги не должны быть доступны через веб-сервер

**Рекомендации:**
- Настроить `.htaccess` или Nginx для блокировки доступа к `.env`, `.settings.php`
- Удалить все бэкапы конфигов из публичных директорий
- Использовать переменные окружения вместо конфигов с секретами

---

## Аутентификация и авторизация

### Одноразовые пароли (OTP)

**Механизм:**
1. Генерация 6-значного кода через `Auth::generatePassword()`
2. Сохранение в поле пароля пользователя
3. Отправка SMS с кодом
4. Авторизация через `$USER->Login()`

**Защита от повторного использования:**

**Файл:** `app/local/php_interface/lib/events/main/User.php`  
**Метод:** `invalidateOtpPassword()`  
**Событие:** `main:OnAfterUserAuthorize` (приоритет 300)

```php
public static function invalidateOtpPassword($ar)
{
    $id = (int)($ar['user_fields']['ID'] ?? 0);
    if ($id <= 0) return;

    global $USER;
    if ($USER->IsAdmin()) return; // Админов не трогаем

    // Генерируем новый случайный пароль
    $newPass = Random::getString(32);
    $ob = new \CUser();
    $ob->Update($id, ['PASSWORD' => $newPass, 'CONFIRM_PASSWORD' => $newPass]);
}
```

**Правила:**
- После успешной авторизации по OTP для не-администраторов пароль сбрасывается на случайный 32-символьный
- Одноразовый код нельзя использовать повторно (в т.ч. для входа в `/bitrix/admin/`)
- Администраторы не затрагиваются (могут использовать OTP для входа в админку)

### Проверка прав доступа

**Проверка администратора:**
```php
global $USER;
if ($USER->IsAuthorized() && $USER->IsAdmin()) {
    // Административные операции
}
```

**Проверка авторизации:**
```php
if (!$USER->IsAuthorized()) {
    // Редирект на страницу авторизации
}
```

---

## Обработка пользовательского ввода

### Использование Ctx::request() вместо суперглобальных массивов

**✅ Правильно:**
```php
use \App\Ctx;

// Получение GET параметра
$orderId = Ctx::request()->get('order_id');

// Получение POST параметра
$phone = Ctx::request()->getPost('phone');

// Получение с проверкой типа
$id = (int)Ctx::request()->get('id');
```

**❌ Неправильно:**
```php
// Прямое использование суперглобальных массивов
$orderId = $_GET['order_id'];
$phone = $_POST['phone'];
```

### Валидация и санитизация

**Валидация телефона:**
```php
use \App\Phone;

$phone = Phone::normalize($input);
if (!$phone) {
    // Ошибка валидации
}
```

**Валидация email:**
```php
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // Ошибка валидации
}
```

**Санитизация строк:**
```php
// Для HTML
$safe = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');

// Для SQL (через ORM Bitrix)
$safe = $sqlHelper->forSql($input);

// Для URL
$safe = urlencode($input);
```

### Фильтрация чувствительных данных из логов

**Файл:** `app/local/php_interface/lib/events/form/OnBeforeResultAdd.php`

```php
// ✅ Правильно: исключение чувствительных данных перед логированием
$req = $_REQUEST;
unset($req['USER_PASSWORD'], $req['USER_CONFIRM_PASSWORD'], $req['sc_resp']);
ulogging([
    'method' => 'OnBeforeResultAdd',
    '$_REQUEST' => $req, // Без паролей и токенов
    'ip' => $_SERVER['HTTP_X_REAL_IP'] ?? null
], '_checkSmartCaptchaForm');
```

**Правила:**
- Не логировать пароли, токены, секретные ключи
- Не логировать полные объекты запросов без фильтрации
- Использовать маскирование для частичной информации (например, `****1234` для карт)

---

## Валидация параметров запроса

### Проблема
Параметры запроса могут содержать неожиданные значения, которые могут привести к XSS, инъекциям или другим атакам.

### Правила валидации

**✅ Правильно:**
```php
// Файл: app/local/components/app/catalog.smart.filter/component.php
$bxajaxid = Ctx::request()->get('bxajaxid');
if ($bxajaxid && preg_match('/^[a-zA-Z0-9_-]+$/', $bxajaxid)) {
    // Использовать $bxajaxid
} else {
    // Ошибка валидации или значение по умолчанию
}
```

**Правила:**
- Всегда валидировать параметры запроса перед использованием
- Использовать регулярные выражения для проверки формата (например, `preg_match('/^[a-zA-Z0-9_-]+$/', $value)`)
- Для ID использовать явное приведение к типу `(int)` и проверку `> 0`
- Для строк использовать `htmlspecialchars()` при выводе
- Для булевых значений использовать строгое сравнение (`=== true` или `=== 'Y'`)

**Примеры валидации:**

```php
// ID: только положительные целые числа
$id = (int)Ctx::request()->get('id');
if ($id <= 0) {
    // Ошибка
}

// Токены: только буквы, цифры, дефисы и подчеркивания
$token = Ctx::request()->get('token');
if (!$token || !preg_match('/^[a-zA-Z0-9_-]+$/', $token)) {
    // Ошибка
}

// Email: валидация формата
$email = Ctx::request()->get('email');
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // Ошибка
}

// URL: валидация через parse_url
$url = Ctx::request()->get('url');
$parsed = parse_url($url);
if (!$parsed || !isset($parsed['scheme']) || !in_array($parsed['scheme'], ['http', 'https'])) {
    // Ошибка
}
```

---

## Защита от загрузки файлов

### Проблема
Загрузка файлов без проверки может привести к:
- Загрузке вредоносных файлов (shell-скрипты, вирусы)
- DoS атакам через большие файлы
- Переполнению диска

### Правила валидации файлов

**✅ Правильно:**
```php
// Файл: app/api/user/save-profile.php
$photoFile = $request->getFile('PERSONAL_PHOTO');
if (is_array($photoFile) && !empty($photoFile['name'])) {
    // Проверка размера (например, максимум 5MB)
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($photoFile['size'] > $maxSize) {
        $result->addError(new Error('Размер файла превышает допустимый'));
        return;
    }
    
    // Проверка типа файла по MIME-типу
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($photoFile['type'], $allowedTypes)) {
        $result->addError(new Error('Недопустимый тип файла'));
        return;
    }
    
    // Проверка расширения файла
    $extension = strtolower(pathinfo($photoFile['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($extension, $allowedExtensions)) {
        $result->addError(new Error('Недопустимое расширение файла'));
        return;
    }
    
    // Дополнительная проверка содержимого файла (magic bytes)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $detectedType = finfo_file($finfo, $photoFile['tmp_name']);
    if (!in_array($detectedType, $allowedTypes)) {
        $result->addError(new Error('Несоответствие типа файла'));
        return;
    }
    finfo_close($finfo);
    
    // Если все проверки пройдены
    $photoFile['old_file'] = $user['PERSONAL_PHOTO'];
    $arFields['PERSONAL_PHOTO'] = $photoFile;
}
```

**Правила:**
- Всегда проверять размер файла (максимальный лимит)
- Проверять MIME-тип файла (`$file['type']`)
- Проверять расширение файла (`pathinfo($file['name'], PATHINFO_EXTENSION)`)
- Использовать `finfo_file()` для проверки реального типа файла (magic bytes)
- Использовать белый список разрешенных типов и расширений
- Сохранять файлы вне публичной директории, если возможно
- Переименовывать загруженные файлы (не использовать оригинальное имя)
- Ограничивать доступ к загруженным файлам через веб-сервер

**❌ Неправильно:**
```php
// Отсутствие проверок
$arFields['PERSONAL_PHOTO'] = $photoFile; // ❌ Опасно!

// Проверка только расширения
if (pathinfo($file['name'], PATHINFO_EXTENSION) === 'jpg') {
    // ❌ Расширение можно подделать
}
```

---

## Защита от DoS атак

### Ограничение размера запросов

**Проблема:** Большие JSON payload или POST запросы могут привести к DoS атаке.

**✅ Правильно:**
```php
// Файл: app/api/core.php
$rawPostData = trim($request->getInput());
$maxSize = 1024 * 1024; // 1MB

if (strlen($rawPostData) > 0) {
    if (strlen($rawPostData) > $maxSize) {
        $this->addError(new Error('Размер данных превышает допустимый'));
        return;
    }
    $postData = json_decode($rawPostData, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        $this->addError(new Error('Неверный формат JSON'));
        return;
    }
}
```

**Правила:**
- Устанавливать максимальный размер для JSON payload (например, 1MB)
- Проверять результат `json_decode()` на ошибки (`json_last_error()`)
- Настраивать `post_max_size` и `upload_max_filesize` в `php.ini`
- Использовать rate limiting на уровне веб-сервера (Nginx/Fail2ban)

### Rate Limiting

**Рекомендации:**
- Настроить rate limiting на уровне Nginx/Fail2ban для:
  - `/api/order/` — создание и изменение заказов
  - `/api/user/save-profile.php` — изменение профиля
  - `/api/basket/` — операции с корзиной
- Ограничить количество запросов с одного IP (например, 100 запросов в минуту)
- Особое внимание к webhook эндпоинтам (`/api/webhook/`)

---

## JavaScript безопасность

### Использование CUtil::PhpToJSObject

**Проблема:** Если данные содержат пользовательский ввод без санитизации, возможен XSS через JavaScript injection.

**✅ Правильно:**
```php
// Санитизация данных перед передачей в JavaScript
$safeDataLayer = [];
foreach ($dataLayer as $key => $value) {
    if (is_string($value)) {
        // Экранирование строковых значений
        $safeDataLayer[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    } elseif (is_array($value)) {
        // Рекурсивная санитизация массивов
        $safeDataLayer[$key] = array_map(function($item) {
            return is_string($item) ? htmlspecialchars($item, ENT_QUOTES, 'UTF-8') : $item;
        }, $value);
    } else {
        $safeDataLayer[$key] = $value;
    }
}
?>
<script>
if (window.dataLayer) window.dataLayer.push(<?= \CUtil::PhpToJSObject($safeDataLayer) ?>);
</script>
```

**Правила:**
- Всегда санитизировать пользовательские данные перед передачей в JavaScript
- Использовать `htmlspecialchars()` для строковых значений
- Для массивов использовать рекурсивную санитизацию
- Если данные приходят из доверенного источника (внутренние данные), санитизация может быть необязательна, но рекомендуется

**Файлы, требующие внимания:**
- `app/ordererror/index.php:11`
- `app/local/templates/trimiata/components/app/catalog.section/main/component_epilog.php:116, 128`
- `app/thankyou/index.php:27`
- `app/local/templates/trimiata/footer.php:116`
- `app/local/content_include/yandexmetrika.php:74, 75, 83`

### Использование eval()

**Проблема:** `eval()` выполняет произвольный JavaScript код и может привести к XSS атаке.

**Файл:** `app/local/php_interface/lib/app/Seo/Content/Helper.php:59-60`

**❌ Опасно:**
```javascript
if (data.reviews) eval("with (reviews) {" + data.reviews + "}");
if (data.model) eval("with (model) {" + data.model + "}");
```

**Правила:**
- Избегать использования `eval()` в JavaScript коде
- Если `eval()` необходим, валидировать данные перед использованием
- Рассмотреть альтернативы: `JSON.parse()`, серверный парсинг
- Добавить комментарий о риске использования `eval()`
- Если данные приходят с внешнего API, убедиться, что источник доверенный

**Рекомендации:**
- Перейти на безопасный парсинг данных (JSON.parse, серверный парсинг)
- Добавить валидацию данных перед использованием `eval()`
- Рассмотреть использование шаблонизаторов вместо `eval()`

---

## Логирование и мониторинг

### Логирование безопасности

**Типы логов:**
- `_blockScanners_requestStarts` — блокировки по началу URL
- `_blockScanners_requestIncludes` — блокировки по подстроке в URL
- `_blockScanners_noReferer` — блокировки запросов без реферера
- `_checkSmartCaptchaForm` — ошибки проверки капчи

**Функция логирования:**
```php
ulogging($data, $logName, $append = true, $includeServer = true);
```

**Правила:**
- Логировать все попытки блокировки сканеров
- Логировать ошибки авторизации и проверки CSRF
- Не логировать чувствительные данные (пароли, токены)
- Регулярно проверять логи на аномалии

### Фильтрация чувствительных данных из $_SERVER

**✅ Правильно:**
```php
// Файл: app/local/php_interface/lib/events/main/BeforeProlog.php
// Фильтрация чувствительных данных из $_SERVER перед логированием
$serverData = $_SERVER;
$sensitiveHeaders = [
    'HTTP_AUTHORIZATION',
    'HTTP_COOKIE',
    'HTTP_X_API_KEY',
    'HTTP_X_CSRF_TOKEN',
    'HTTP_X_BITRIX_CSRF_TOKEN'
];
foreach ($sensitiveHeaders as $header) {
    if (isset($serverData[$header])) {
        unset($serverData[$header]);
    }
}
ulogging($serverData, '_blockScanners_' . $checkType, true, true);
```

**Правила:**
- Всегда фильтровать чувствительные заголовки из `$_SERVER` перед логированием
- Удалять заголовки: `HTTP_AUTHORIZATION`, `HTTP_COOKIE`, `HTTP_X_API_KEY`, `HTTP_X_CSRF_TOKEN`, `HTTP_X_BITRIX_CSRF_TOKEN`
- Не логировать полные объекты `$_REQUEST` или `$_SERVER` без фильтрации
- Использовать копию массива для фильтрации (`$serverData = $_SERVER;`)

### Мониторинг

**Рекомендации:**
- Настроить алерты на частые блокировки с одного IP
- Мониторить ошибки 403 (возможные ложные срабатывания)
- Отслеживать попытки доступа к админ-панелям
- Анализировать логи на паттерны атак

---

## Защита API эндпоинтов

### Структура API

**Базовый файл:** `app/api/core.php`

**Проверки безопасности:**
1. Проверка CSRF токена (для критичных операций)
2. Проверка авторизации (где необходимо)
3. Валидация входных данных
4. Проверка прав доступа

### Исключения из проверки CSRF

**Публичные эндпоинты (только чтение):**
- `/api/1c/` — интеграция с 1С
- `/api/basket/` — операции с корзиной
- `/api/location/` — определение местоположения
- `/api/modal/` — модальные окна
- `/api/news/` — новости
- `/api/product/` — информация о товарах
- `/api/user/` — публичные данные пользователя

**Критичные операции (требуют CSRF):**
- Создание заказа
- Изменение данных пользователя
- Операции с платежами
- Административные операции

### Валидация входных данных

```php
// ✅ Правильно: валидация и приведение типов
$id = (int)Ctx::request()->get('id');
if ($id <= 0) {
    $this->addError(new Error('Неверный идентификатор'));
    return;
}

// ✅ Правильно: валидация обязательных полей
$phone = Ctx::request()->getPost('phone');
if (!$phone || !Phone::normalize($phone)) {
    $this->addError(new Error('Неверный номер телефона'));
    return;
}
```

### Rate Limiting

**Рекомендации:**
- Настроить rate limiting на уровне Nginx/Fail2ban
- Ограничить количество запросов с одного IP
- Особое внимание к webhook эндпоинтам (`/api/webhook/`)

---

## Отладочный код в проде

### Проблема
Отладочный код может привести к утечке информации о структуре кода, данных и логике приложения.

### Правила

**❌ Неправильно:**
```php
// Использование GET параметра для отладки
if ($USER->IsAdmin() && isset($_REQUEST['debug']) && $_REQUEST['debug'] == 'y') {
    pre($o, $die, debug_backtrace());
}
```

**✅ Правильно:**
```php
// Использование константы или переменной окружения
if ($USER->IsAdmin() && (defined('APP_DEBUG') && APP_DEBUG)) {
    pre($o, $die, debug_backtrace());
}

// Или полностью отключить в проде:
if ($USER->IsAdmin() && APP_IS_DEV && isset($_REQUEST['debug']) && $_REQUEST['debug'] == 'y') {
    pre($o, $die, debug_backtrace());
}
```

**Правила:**
- Не использовать GET/POST параметры для включения отладки в проде
- Использовать константы или переменные окружения (`APP_DEBUG`, `APP_IS_DEV`)
- Отключать отладочный код в продакшене
- Не логировать `debug_backtrace()` в проде
- Не выводить детальную информацию об ошибках в проде (использовать общие сообщения)

**Файл:** `app/local/php_interface/inc/functions.php:340`

---

## Инфраструктурная безопасность

### Сессии

**Хранение:** Redis (в проде)  
**Конфигурация:** `.settings.php`

**Рекомендации:**
- Использовать secure cookies при HTTPS (`cookies.secure=true`)
- Установить `HttpOnly` для cookies
- Использовать `SameSite` атрибут для защиты от CSRF

### Кеширование

**Хранение:** Memcached  
**Конфигурация:** `.settings.php`

**Безопасность:**
- Кеш не должен содержать чувствительных данных
- Использовать теги кеша для инвалидации
- Ограничить время жизни кеша для критичных данных

### HTTPS

**Требования:**
- Все соединения должны использовать HTTPS
- Редиректы с HTTP на HTTPS
- HSTS заголовки для принудительного HTTPS

### Заголовки безопасности

**Content Security Policy (CSP):**
```php
<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests" />
```

**Рекомендации для расширения:**
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY` или `SAMEORIGIN`
- `X-XSS-Protection: 1; mode=block`
- `Referrer-Policy: strict-origin-when-cross-origin`

**Рекомендация:** Добавить полный CSP заголовок через HTTP заголовки:
```
Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-{random}'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; connect-src 'self' https://api.openai.com;
```

---

## Best Practices для разработчиков

### Правила кодирования

1. **Всегда экранировать вывод:**
   ```php
   <?= htmlspecialchars($data, ENT_QUOTES, 'UTF-8') ?>
   ```

2. **Использовать Ctx::request() вместо $_GET/$_POST:**
   ```php
   $value = Ctx::request()->get('param');
   ```

3. **Валидировать и приводить типы:**
   ```php
   $id = (int)Ctx::request()->get('id');
   if ($id <= 0) return;
   ```

4. **Проверять права доступа:**
   ```php
   global $USER;
   if (!$USER->IsAuthorized() || !$USER->IsAdmin()) {
       // Ошибка доступа
   }
   ```

5. **Использовать подготовленные запросы или ORM:**
   ```php
   ModelTable::getList(['filter' => ['=ID' => $id]]);
   ```

6. **Не логировать чувствительные данные:**
   ```php
   $logData = $requestData;
   unset($logData['password'], $logData['token']);
   ulogging($logData, 'log_name');
   ```

7. **Использовать переменные окружения для секретов:**
   ```php
   $apiKey = $_ENV['API_KEY'] ?? '';
   ```

8. **Проверять CSRF для критичных операций:**
   ```php
   if (!check_bitrix_sessid()) {
       // Ошибка CSRF
   }
   ```

9. **Валидировать URL перед редиректом:**
   ```php
   $parsedUrl = parse_url($url);
   if (!$parsedUrl || (!isset($parsedUrl['host']) || $parsedUrl['host'] === $currentHost)) {
       LocalRedirect($url);
   }
   ```

10. **Проверять владельца ресурса перед изменением:**
    ```php
    if ($resourceUserId !== $currentUserId) {
        // Ошибка доступа
    }
    ```

11. **Валидировать параметры запроса:**
    ```php
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $param)) {
        // Ошибка валидации
    }
    ```

12. **Проверять размер и тип загружаемых файлов:**
    ```php
    if ($file['size'] > $maxSize || !in_array($file['type'], $allowedTypes)) {
        // Ошибка
    }
    ```

13. **Фильтровать $_SERVER перед логированием:**
    ```php
    $serverData = $_SERVER;
    unset($serverData['HTTP_AUTHORIZATION'], $serverData['HTTP_COOKIE']);
    ulogging($serverData, 'log_name');
    ```

14. **Санитизировать данные перед передачей в JavaScript:**
    ```php
    $safe = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    ```

15. **Не использовать GET параметры для отладки в проде:**
    ```php
    if (APP_DEBUG) { // Использовать константу, не $_REQUEST['debug']
        // Отладка
    }
    ```

### Чеклист безопасности при разработке

- [ ] Все пользовательские данные валидируются и санитизируются
- [ ] Вывод экранируется через `htmlspecialchars()`
- [ ] Используется `Ctx::request()` вместо суперглобальных массивов
- [ ] Проверяются права доступа перед критичными операциями
- [ ] CSRF токены проверяются для изменяющих операций
- [ ] Секреты хранятся в переменных окружения
- [ ] Чувствительные данные не логируются
- [ ] SQL запросы используют ORM или подготовленные запросы
- [ ] Новые API эндпоинты проверяют авторизацию и CSRF
- [ ] Проверены конфликты паттернов блокировки с реальными URL
- [ ] URL валидируются перед редиректом (защита от Open Redirect)
- [ ] Проверяется владелец ресурса перед изменением/удалением
- [ ] Параметры запроса валидируются через регулярные выражения
- [ ] Размер и тип загружаемых файлов проверяются
- [ ] Размер JSON payload ограничен
- [ ] Данные санитизируются перед передачей в JavaScript
- [ ] Отладочный код отключен в проде

### Регулярный аудит безопасности

**Периодичность:** ежемесячно

**Проверки:**
1. Анализ логов блокировок на ложные срабатывания
2. Проверка наличия секретов в коде (grep по паттернам)
3. Аудит прав доступа к критичным операциям
4. Проверка обновлений безопасности зависимостей
5. Анализ новых векторов атак и обновление списков блокировки
6. Проверка использования `eval()` в JavaScript
7. Проверка отладочного кода в проде
8. Аудит валидации параметров запроса
9. Проверка защиты от загрузки файлов
10. Проверка ограничений размера запросов

---

## Ссылки и ресурсы

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Bitrix Security Documentation](https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43)
- [PHP Security Best Practices](https://www.php.net/manual/en/security.php)
- Конфигурация блокировки: `app/local/php_interface/config/blocker.php`
- Обработчик блокировки: `app/local/php_interface/lib/events/main/BeforeProlog.php`

---

**Последнее обновление:** 2026-01-27  
**Версия документа:** 2.0
