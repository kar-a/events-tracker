## Интеграции — **events.trimiata.ru**

### Продьюсеры событий → collector
- **Браузер / PWA (trimiata.ru)** и **мобильное приложение** отправляют HTTP‑запросы на хост **events.trimiata.ru** (коллектор), тело — по контракту из `system/events-service/packages/contract/`.
- Сайт на Bitrix **не** обязан жить в этом репозитории; связь — только по **HTTPS API** и согласованной схеме событий (см. [event-contract.md](../system/events-service/docs/event-contract.md)).
- Наблюдаемость: Grafana, метрики сервиса — `system/events-service/infra/grafana/`.

Интеграции **основного магазина** (ImShop, 1С, Dadata и т.д.) — в разделах ниже.

---

## Интеграции и точки входа (основной сайт — кратко)

### ImShop (мобильное приложение)
- HTTP API: `app/api/webhook/*`
- Бизнес‑логика: `App\\Order\\External\\ImShop`
- Избранное (тестовая синхронизация):
  - Вебхук ImShop → сайт: `POST /api/webhook/?method=wishlist` → `ImShop::wishlist()`; payload: `{ externalUserId, wishlist:[int] }` или `{ userId, wishlist: [{id, hidden, syncedOn}] }` в тестовой версии; на сайте нормализуется массив и сохраняется (временная реализация), целевое поведение — через `\App\Catalog\Helper::setWishlistItems()`.
  - Сайт → ImShop: `/api/product/wishlist.php` после локального `setWishlistItems()` добавляет в очередь `Exchange::addToStackExternalRequest(Exchange::SEND_WISHLIST_METHOD, Exchange::SERVICE_IMSHOP, { wishlist, userId })`. В `ImShop::sendWishlist()` формируется запрос к endpoint `wishlist/sync/` (см. `getServiceMethod`).
  - Очередь: `Exchange::SEND_WISHLIST_METHOD`, post‑send хук `ImShop::onAfterSendWishlistSend()` отмечает запись успешной.
  - Принципы: не блокировать UI сетевыми вызовами; списки уникализировать; формат ID строго числовой; метод в webhook‑роутере — алфавитный порядок.

### 1С
- Точки входа: `app/api/1c/*`, очередь обмена — `Exchange::*`

### Dadata.ru
- `App\\Suggestions\\DadataService` — адресные подсказки

### Платежи и доставка
- Платёжные системы/доставки — маппинги в `App\\Order\\Helper`, расчёты в событиях `sale:*`

## Интеграции и точки входа (основной сайт — подробно)

### ImShop (мобильное приложение)
- HTTP API: `app/api/webhook/*`
  - `get-delivery-methods.php`, `get-pay-systems.php`, `calculate-basket.php`, `create-order.php`, `process-payment.php`, `orders-history.php`, `order-set-status.php`, `get-personal-price.php`, `get-products-availability.php`, `get-shops-list.php`, `wishlist.php`.
- Бизнес-логика: `App\Order\External\ImShop`.
- Привязка торговой платформы: `TRADING_PLATFORM_IMSHOP = 2`.
- Особенности:
  - Создание/калькуляция заказа — через внутренний конструктор `initOrder`, поддержка купонов, персональных цен (ограничение общей скидки `<=10%`).
  - Платежи: `processPayment()` инициирует оплату PS‑сервисом, специальные ветки для `Tinkoff`, `OTP`, QR.
  - Доставка/самовывоз: нормализация названий EMS/DPD, карта магазинов для самовывоза `Store\Helper`.

### 1С
- Точки входа: `app/api/1c/*` (регистрация изменений, выгрузки, смена статусов).
- Очередь обмена: вызовы вида `App\Exchange::addToStack*` в обработчиках заказов.

### Dadata.ru
- Класс `App\\Suggestions\\DadataService`: адресные подсказки, поиск по ID/FIAS; использует `APP_DADATA_API_KEY`/`APP_DADATA_SECRET`.

### Платежи
- Идентификаторы платёжных систем в `App\\Order\\Helper` (`PAY_SYSTEM_*`), интегрируются через стандартный Bitrix `PaySystem\Manager` (инициация оплаты, ссылки, статусы).

### Доставка
- Карта ID доставок в `App\\Order\\Helper` (`DELIVERY_TYPE_*`), типы доставки (pickup/delivery), пересчёт цены доставки и округление в хендлерах `sale:onSaleDeliveryServiceCalculate`.

### Яндекс
- SmartCaptcha: `App\\Yandex\\SmartCaptcha`.
- Webmaster: `App\\Yandex\\Webmaster`.
- YML/Yandex Market: класс `App\\Export\\Yml`, события `yandexmarket/*`.



