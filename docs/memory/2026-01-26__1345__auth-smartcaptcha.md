# 1345: SmartCaptcha в авторизации

## Context
Невидимая Yandex SmartCaptcha: `SmartCaptcha::getCode($id)` → `smartCaptcha.render(..., { invisible: true })`, токен в `sc_resp`, событие `scTokenSetted`. Клиент: `AppForm.checkSmartCaptcha()` → `execute()` → токен в hidden → отправка. Сервер веб-форм: `OnBeforeResultAdd::checkSmartCaptcha`.

## Key Decisions
- Параметр компонента `USE_SMART_CAPTCHA` (`Y` в `/auth/` и `api/modal/auth`).
- Проверка до `switch ($action)` для трёх действий; без токена — `ERROR_MESSAGE` как у форм.
- **Исключение:** POST `login_or_register` + `phoneConfirmation=Y` (AppForm.confirmPhone → открытие попапа с заказа) — капча не требуется, иначе сломается сценарий без виджета на странице заказа.

## Code Touchpoints
- `app/local/components/app/system.auth.full/class.php`
- `app/auth/index.php`, `app/api/modal/auth.php`
- Шаблоны `system.auth.full`: hidden `sc_resp`, `SmartCaptcha::getCode` в `main/template.php` и `popup/template.php`

## Verification
Вход по телефону/email, ввод кода; попап с главной; страница `/auth/`. Сценарий: телефон в форме заказа → попап подтверждения — без капчи на первом шаге.

## Follow-ups
При необходимости закрыть исключение — добавить капчу на страницу оформления или отдельный токен.
