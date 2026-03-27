# 1191 — Checkout: Информация о рассрочках (период охлаждения)

Context
- Задача: при выборе рассрочки на /checkout/ показывать инфо‑попап о «периоде охлаждения», если сумма ≥ 50000.
- Используются offcanvas/CupertinoPane; динамика модалей через `App.loadAjaxAside('order-payment-installment','api', params, true)`.

Key Decisions
- Сервер помечает рассрочные ПС через `data-role="payment-credit-popup"` (стартовый шаблон и AJAX‑рендер).
- Источник суммы — атрибут `data-raw-total` у `[data-total]` (без парсинга форматированной строки).
- Бизнес‑правила часов централизованы: `App\Order\Helper::getCreditCoolingHoursByPrice()`; `App\Exchange\Exchange::getCreditUnpaidTimeoutMinutesByPrice()` использует её.
- Модаль `app/api/modal/order-payment-installment.php` принимает `total` и выводит часы с помощью `App\Str::endings()`.

Code Touchpoints
- Frontend: `app/local/templates/trimiata/dist/js/checkout.js` — слушатель change платежей, проверка `data-role`, чтение `data-raw-total`, вызов Aside.
- Backend (AJAX): `app/local/components/koptelnya/checkout/ajax.php` — добавляет `data-role` к radio рассрочки; возвращает `total_raw`.
- Template: `.../checkout/templates/.default/form.php` — стартовый `data-role` и `data-raw-total`.
- Modal API: `app/api/modal/order-payment-installment.php` — Str::endings, Helper::getCreditCoolingHoursByPrice.
- Domain: `App\Order\Helper`, `App\Exchange\Exchange` — унификация правил.

Gotchas
- Не парсить форматированную строку суммы — использовать `data-raw-total`.
- Единая метка `data-role="payment-credit-popup"` должна совпадать в шаблоне и AJAX.
- Учитывать мобильный режим (CupertinoPane) — использовать существующую обвязку Aside.

Verification
- /checkout/: переключение на рассрочку при сумме <50000 — попап не показывается.
- Сумма 50k–200k — «4 часа»; >200k — «48 часов»; склонение через `Str::endings`.
- Повторный AJAX после смены доставки/локации — `data-raw-total` обновляется и логика сохраняется.

Follow-ups
- Покрыть E2E тестом событийную ветку (change pay_system_id → show Aside) и ветку суммы.
- Добавить трекинг события показа инфо‑попапа в Метрику при необходимости.

