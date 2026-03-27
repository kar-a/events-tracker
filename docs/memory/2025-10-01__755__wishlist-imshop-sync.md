# 755 — Тестовая двусторонняя синхронизация избранного с ImShop

## Context
- Требование: выровнять список избранного между сайтом и мобильным приложением ImShop, не блокируя UI, с гарантированной доставкой.
- Архитектура проекта: внешние интеграции через очередь `App\Exchange`, ImShop‑вебхуки под `/api/webhook/*`, бизнес‑логика — `App\Order\External\ImShop`.

## Key Decisions
- ImShop → сайт: webhook `POST /api/webhook/?method=wishlist` делегируется в `ImShop::wishlist()`; формат допускает `{ externalUserId, wishlist:[int] }` и тестовый `{ userId, wishlist:[{id,hidden,syncedOn}] }`.
- Сайт → ImShop: при изменении `/api/product/wishlist.php` ставит в очередь `Exchange::SEND_WISHLIST_METHOD` (service IMShop); `ImShop::sendWishlist()` дергает endpoint `wishlist/sync/`.
- Неблокирующая модель через очередь; уникализация ID.

## Code Touchpoints
- `app/api/webhook/wishlist.php` — точка входа вебхука.
- `app/api/webhook/index.php` — алфавитный `$methodsMap` включает `wishlist`.
- `app/local/php_interface/lib/app/Order/External/ImShop.php` — методы `wishlist()`, `sendWishlist()`, `getServiceMethod()`, `getMethodsMap()`, `onAfterSendWishlistSend()`.
- `app/api/product/wishlist.php` — постановка задачи в очередь после локальной синхронизации.
- `app/local/php_interface/lib/app/Exchange/Exchange.php` — `SEND_WISHLIST_METHOD` в `STACK_METHODS`.

## Gotchas
- Формат payload от ImShop может отличаться (int[] vs объектный список) — тестовая версия допускает оба.
- Защита вебхука (аутентификация) — к продакшену понадобится токен/подпись.
- Не блокировать UI: любые запросы наружу через очередь.

## Verification
- POST `/api/webhook/?method=wishlist` → `{success:true}` и актуальный список на сайте.
- POST `/api/product/wishlist.php?action=add|delete` → задача в очереди (`ExchangeStack`) с `SEND_WISHLIST_METHOD` и корректными параметрами.

## Follow-ups
- Добавить auth для вебхука (HMAC/токен), расширить логику `wishlist()` до вызова `Catalog\Helper::setWishlistItems()` и маппинга `externalUserId`.
- Покрыть кейсы мерджа списка (конфликты сайт↔приложение), ретраи/алерты по очереди.


