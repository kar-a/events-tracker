# Аудит безопасности проекта Trimiata

**Дата проведения:** 2026-01-27  
**Версия документа безопасности:** 1.0  
**Проверено файлов:** ~150+ PHP файлов

---

## Резюме

Проведен аудит безопасности кода проекта согласно документу `docs/security.md`. Найдено **8 критичных проблем**, **12 предупреждений** и **5 рекомендаций** для улучшения безопасности.

**Статус:** Исправлено 8 критичных проблем и 3 предупреждения. Осталось 0 критичных проблем и 9 предупреждений, требующих внимания.

---

## Критичные проблемы (требуют немедленного исправления)

*Все критичные проблемы исправлены ✅*

---

## Предупреждения (требуют внимания)

### 1. Использование CUtil::PhpToJSObject без санитизации данных

**Файлы:**
- `app/ordererror/index.php:11`
- `app/local/templates/trimiata/components/app/catalog.section/main/component_epilog.php:116, 128`
- `app/thankyou/index.php:27`
- `app/local/templates/trimiata/footer.php:116`
- `app/local/content_include/yandexmetrika.php:74, 75, 83`

**Проблема:**
```php
// app/ordererror/index.php:11
<script>if (window.dataLayer) window.dataLayer.push(<?= \CUtil::PhpToJSObject($dataLayer) ?>);</script>
```

**Риск:** Если `$dataLayer` содержит пользовательские данные без санитизации, возможен XSS через JavaScript injection.

**Анализ:**
- В большинстве случаев `$dataLayer` формируется из внутренних данных (заказы, товары)
- Но если в данных есть пользовательский ввод (имя, комментарии), возможен XSS

**Исправление:**
```php
// ✅ Правильно: санитизация данных перед передачей в JavaScript
// Для пользовательских данных использовать json_encode с экранированием
$safeDataLayer = [];
foreach ($dataLayer as $key => $value) {
    if (is_string($value)) {
        $safeDataLayer[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    } else {
        $safeDataLayer[$key] = $value;
    }
}
?>
<script>if (window.dataLayer) window.dataLayer.push(<?= \CUtil::PhpToJSObject($safeDataLayer) ?>);</script>
```

**Приоритет:** 🟡 Средний (зависит от источника данных)

---

### 2. Использование $_REQUEST['debug'] для отладки

**Файл:** `app/local/php_interface/inc/functions.php:340`

**Проблема:**
```php
if ($USER->IsAdmin() && isset($_REQUEST['debug']) && $_REQUEST['debug'] == 'y') {
    pre($o, $die, debug_backtrace());
}
```

**Риск:** 
- Отладочная информация может попасть в логи
- Возможна утечка информации о структуре кода через `debug_backtrace()`

**Исправление:**
```php
// ✅ Правильно: использовать константу или переменную окружения вместо GET параметра
if ($USER->IsAdmin() && (defined('APP_DEBUG') && APP_DEBUG)) {
    pre($o, $die, debug_backtrace());
}

// Или полностью отключить в проде:
if ($USER->IsAdmin() && APP_IS_DEV && isset($_REQUEST['debug']) && $_REQUEST['debug'] == 'y') {
    pre($o, $die, debug_backtrace());
}
```

**Приоритет:** 🟡 Средний (только для админов, но лучше убрать)

---

### 3. Использование $_GET/$_POST в компонентах Bitrix

**Файлы:**
- `app/local/components/app/catalog.smart.filter/class.php:1193, 1195`
- `app/local/components/app/catalog.search.full/class.php:412, 451`

**Проблема:** Прямое использование `$_GET`, `$_POST`, `$_REQUEST` вместо `Ctx::request()`.

**Рекомендация:** Унифицировать использование `Ctx::request()` во всех компонентах.

**Приоритет:** 🟢 Низкий (работает, но не соответствует стандартам проекта)

---

### 4. Логирование без проверки на чувствительные данные

**Файлы:**
- `app/local/php_interface/lib/events/sale/Order.php` — множественные вызовы `ulogging()`
- `app/local/php_interface/lib/app/Exchange/Trimiata1c/Trimiata1c.php` — логирование обмена

**Рекомендация:** Проверить все логи на наличие чувствительных данных (пароли, токены, персональные данные).

**Приоритет:** 🟡 Средний

---

### 5. Отсутствие rate limiting на API эндпоинтах

**Проблема:** Нет ограничения количества запросов с одного IP.

**Рекомендация:** Настроить rate limiting на уровне Nginx/Fail2ban для:
- `/api/order/` — создание и изменение заказов
- `/api/user/save-profile.php` — изменение профиля
- `/api/basket/` — операции с корзиной

**Приоритет:** 🟡 Средний

---

### 6. Отсутствие проверки размера загружаемых файлов

**Файл:** `app/api/user/save-profile.php:66`

**Проблема:**
```php
$photoFile = $request->getFile('PERSONAL_PHOTO');
if (is_array($photoFile) && !empty($photoFile['name'])) {
    $arFields['PERSONAL_PHOTO'] = $photoFile; // ❌ Нет проверки размера и типа
}
```

**Исправление:**
```php
$photoFile = $request->getFile('PERSONAL_PHOTO');
if (is_array($photoFile) && !empty($photoFile['name'])) {
    // Проверка размера (например, максимум 5MB)
    if ($photoFile['size'] > 5 * 1024 * 1024) {
        $result->addError(new Error('Размер файла превышает допустимый'));
    }
    // Проверка типа файла
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($photoFile['type'], $allowedTypes)) {
        $result->addError(new Error('Недопустимый тип файла'));
    }
    if ($result->isSuccess()) {
        $photoFile['old_file'] = $user['PERSONAL_PHOTO'];
        $arFields['PERSONAL_PHOTO'] = $photoFile;
    }
}
```

**Приоритет:** 🟡 Средний

---

### 7. Использование eval() в JavaScript коде

**Файл:** `app/local/php_interface/lib/app/Seo/Content/Helper.php:59-60`

**Проблема:**
```php
if (data.reviews) eval("with (reviews) {" + data.reviews + "}");
if (data.model) eval("with (model) {" + data.model + "}");
```

**Риск:** Если `data.reviews` или `data.model` содержат пользовательские данные, возможен XSS через JavaScript injection.

**Текущее состояние:** Данные приходят с внешнего API (tyresaddict.ru), но источник должен быть доверенным.

**Рекомендация:** 
- Добавить валидацию данных перед использованием `eval()`
- Рассмотреть альтернативы (JSON.parse, серверный парсинг)
- Добавить комментарий о риске (уже добавлен ✅)

**Приоритет:** 🟡 Средний (данные из внешнего API, но лучше перейти на безопасный парсинг)

---

### 8. Отсутствие Content Security Policy заголовков

**Текущее состояние:** Есть только `upgrade-insecure-requests` в мета-теге.

**Рекомендация:** Добавить полный CSP заголовок через HTTP заголовки:
```
Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-{random}'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; connect-src 'self' https://api.openai.com;
```

**Приоритет:** 🟢 Низкий (улучшение безопасности)

---

### 9. Отсутствие проверки типов файлов при загрузке

**Файл:** `app/api/user/save-profile.php:66`

**Проблема:** Нет проверки MIME-типа и расширения файла.

**Исправление:** См. пункт 6.

**Приоритет:** 🟡 Средний

---

### 10. Логирование полных объектов без фильтрации

**Файлы:**
- `app/local/php_interface/lib/app/Exchange/Trimiata1c/Trimiata1c.php:670, 792`
- `app/local/php_interface/lib/events/sale/Order.php` — множественные вызовы

**Рекомендация:** Проверить логи на наличие чувствительных данных и добавить фильтрацию.

**Приоритет:** 🟡 Средний

---

### 11. Отсутствие проверки размера JSON payload

**Файл:** `app/api/core.php:59`

**Проблема:**
```php
$rawPostData = trim($request->getInput());
if (strlen($rawPostData) > 0) {
    $postData = json_decode($rawPostData, true); // ❌ Нет проверки размера
}
```

**Риск:** DoS атака через большой JSON payload.

**Исправление:**
```php
$rawPostData = trim($request->getInput());
$maxSize = 1024 * 1024; // 1MB
if (strlen($rawPostData) > 0) {
    if (strlen($rawPostData) > $maxSize) {
        $this->addError(new Error('Размер данных превышает допустимый'));
        return;
    }
    $postData = json_decode($rawPostData, true);
}
```

**Приоритет:** 🟡 Средний

---

## Рекомендации для улучшения

### 12. Расширение Content Security Policy

**Текущее состояние:** Только `upgrade-insecure-requests`.

**Рекомендация:** Добавить полный CSP через HTTP заголовки с nonce для inline скриптов.

**Приоритет:** 🟢 Низкий

---

### 13. Добавление заголовков безопасности

**Рекомендация:** Добавить HTTP заголовки:
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: SAMEORIGIN`
- `X-XSS-Protection: 1; mode=block`
- `Referrer-Policy: strict-origin-when-cross-origin`

**Приоритет:** 🟢 Низкий

---

### 14. Настройка secure cookies

**Текущее состояние:** `cookies.secure=false` в `.settings.php` (по документации).

**Рекомендация:** Включить `cookies.secure=true` при HTTPS.

**Приоритет:** 🟡 Средний

---

### 15. Регулярный аудит зависимостей

**Рекомендация:** 
- Использовать `composer audit` для проверки уязвимостей
- Настроить автоматические обновления безопасности
- Мониторить CVE для используемых библиотек

**Приоритет:** 🟡 Средний

---

### 16. Добавление мониторинга безопасности

**Рекомендация:**
- Настроить алерты на частые блокировки сканеров
- Мониторить ошибки 403 (ложные срабатывания)
- Отслеживать попытки доступа к админ-панелям
- Анализировать логи на паттерны атак

**Приоритет:** 🟢 Низкий

---

## Положительные моменты

✅ **Хорошо реализовано:**
1. Блокировка сканеров уязвимостей через `checkScanners()` ✅
2. Инвалидация OTP паролей после входа ✅
3. Использование `Ctx::request()` в большинстве мест ✅
4. Экранирование вывода через `htmlspecialchars()` в шаблонах ✅
5. Использование ORM Bitrix D7 для защиты от SQL injection ✅
6. Проверка CSRF в `app/api/user/save-profile.php` ✅
7. Фильтрация чувствительных данных из логов в `OnBeforeResultAdd` ✅
8. Секреты хранятся в переменных окружения ✅
9. Проверка прав доступа в большинстве критичных операций ✅

---

## План исправлений

### Критичные (немедленно):
*Все критичные проблемы исправлены ✅*

### Важные (в течение недели):
1. Добавить проверку размера и типа загружаемых файлов
2. Проверить логирование на наличие чувствительных данных

### Улучшения (в течение месяца):
3. Расширить Content Security Policy
4. Добавить заголовки безопасности
5. Настроить rate limiting
6. Провести полный аудит логов на чувствительные данные

---

## Статистика аудита

- **Проверено файлов:** ~150+ PHP файлов
- **Критичных проблем:** 8 (8 исправлено ✅, 0 осталось)
- **Предупреждений:** 12 (3 исправлено ✅, 9 осталось)
- **Рекомендаций:** 5
- **Положительных моментов:** 9

---

## Выполненные исправления (2026-01-27)

✅ **Исправлено (проверено по коду):**
1. Open Redirect в `app/auth/index.php` — добавлена валидация URL (parse_url, проверка домена).
2. Проверка владельца заказа в `app/api/order/index.php` — проверка USER_ID для `changePaySystem` и `cancelOrder`, доп. проверка сессии для `cancelOrder`.
3. CSRF для `/api/order/` — `/api/order/` убран из `noCheckUrls` в `app/api/core.php`; в `app/api/order/index.php` добавлена проверка `check_bitrix_sessid()` для POST-операций `changePaySystem` и `cancelOrder`.
4. Валидация типов в `app/api/order/index.php` — `(int)Ctx::request()->get('id')`, `(int)$USER->GetID()`.
5. Использование `$_SESSION` в `app/api/order/index.php` — `$_SESSION['SALE_ORDER_ID'] ?? null` при передаче в компонент.
6. Проверка прав доступа в API заказов — явная проверка владельца заказа перед изменением/отменой.
7. Фильтрация чувствительных данных из `$_SERVER` в `BeforeProlog.php` — добавлена фильтрация заголовков (HTTP_AUTHORIZATION, HTTP_COOKIE, HTTP_X_API_KEY и др.) перед логированием в двух местах.
8. Использование `$_GET` в компонентах — заменено на `Ctx::request()->get('bxajaxid')` с валидацией через регулярное выражение в `catalog.smart.filter/component.php` (3 места).

**Следующий аудит:** 2026-02-27 (через месяц)
