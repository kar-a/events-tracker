# Task Memory Card
Date: 2026-01-27
Task Key: 1317 (+ security hardening, ordererror)
Title: Смена пароля после авторизации по одноразовому коду и усиление безопасности

## Context
- Одноразовый код (OTP) из SMS записывается в поле пароля пользователя; после входа пароль не менялся, им можно было войти повторно, в т.ч. в /bitrix/admin/.
- В коде был захардкожен API-ключ OpenAI; в лог при ошибке SmartCaptcha попадал весь $_REQUEST; на ordererror использовался $_GET без экранирования вывода.

## Key Decisions
- Вешать инвалидацию пароля на main:OnAfterUserAuthorize (приоритет 300, до saveUserFields 400). Для не-админов ($USER->IsAdmin() === false) генерировать Random::getString(32) и CUser->Update(PASSWORD, CONFIRM_PASSWORD). Админов не трогать.
- OpenAI: ключ из APP_OPENAI_API_KEY (constants.php из $_ENV['OPENAI_API_KEY']), в OpenAi.php — конфиг openai.api_key или константа; в .env.example добавлена переменная OPENAI_API_KEY.
- Логи: перед ulogging при ошибке SmartCaptcha исключать из копии $_REQUEST ключи USER_PASSWORD, USER_CONFIRM_PASSWORD, sc_resp.
- ordererror: Ctx::request()->get('order_id') и htmlspecialchars при выводе номера заказа.

## Code Touchpoints
- `app/local/php_interface/lib/events/main/User.php`: обработчик invalidateOtpPassword($ar), регистрация OnAfterUserAuthorize 300; use Random.
- `app/local/php_interface/inc/constants.php`: APP_OPENAI_API_KEY из $_ENV['OPENAI_API_KEY'] ?? ''.
- `app/local/php_interface/lib/app/Seo/Content/OpenAi/OpenAi.php`: удалён константный API_KEY, ключ в конструкторе из конфига/APP_OPENAI_API_KEY, сохранён в $this->apiKey; в models() используется $this->apiKey.
- `app/.env.example`: OPENAI_API_KEY=.
- `app/local/php_interface/lib/events/form/OnBeforeResultAdd.php`: копия $_REQUEST без паролей и sc_resp перед ulogging.
- `app/local/php_interface/lib/app/Seo/Content/Helper.php`: комментарий о риске eval с данными tyresaddict.
- `app/ordererror/index.php`: \App\Ctx::request()->get('order_id'), htmlspecialchars($orderId, ENT_QUOTES, 'UTF-8').

## Gotchas (Pitfalls)
- На окружениях нужно задать OPENAI_API_KEY в .env, иначе OpenAI-функции (SEO-контент) не заработают.
- OnAfterUserAuthorize вызывается после каждой успешной авторизации (в т.ч. по логину/паролю), не только по OTP; сброс пароля для не-админов после любого входа — осознанное решение (одноразовый сценарий в основном OTP).

## Verification
- Вход по OTP под не-админом → повторный вход с тем же кодом (и на сайт, и в /bitrix/admin/) невозможен. Админ после входа по OTP сохраняет пароль.
- В репозитории нет строки с sk-… OpenAI. В логах _checkSmartCaptchaForm нет паролей. Страница /ordererror/?order_id=123 отображает экранированный номер.

## Follow-ups
- При смене формата ответа tyresaddict рассмотреть отказ от eval в пользу JSON или серверного парсинга (Seo/Content/Helper.php).
