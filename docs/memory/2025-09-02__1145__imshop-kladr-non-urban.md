## 1145: Службы доставки ImShop для негородских местоположений (KLADR)

Files → `app/local/php_interface/lib/app/Order/External/ImShop.php`, `app/local/php_interface/lib/app/Location/Helper.php`, `.github/workflows/{stage,main}-pull-on-commit.yaml`

Effects →
- Импорт/вебхуки ImShop: исправлена передача/нормализация KLADR для доставки/создания заказа/пересчёта корзины. Поддержаны `cityKladr`/`city_kladr`/`kladr`; `prepareKladr()` обрезает до 13 символов и дополняет нулями.
- CI: уведомления main/stage показывают заголовок последнего коммита сливаемой ветки (вместо «Merge … into …»); единый формат с Commit summary.

Intent →
- Корректные расчёты доставок и оформление заказов для негородских адресов в приложении; предсказуемая география.
- Понятные и единообразные уведомления деплоев для команды.

### Context
- Ошибка: KLADR приходил в разных ключах и иногда «сырой» (>13 символов), что ломало выбор служб доставки.
- Merge‑уведомления в CI брали заголовок merge‑коммита, а не содержательный subject из сливаемой ветки.

### Key Decisions
- В `ImShop.php` в методах `getDeliveryMethods`, `createOrder`, `calculateBasket` брать KLADR из `cityKladr`→`city_kladr`→`kladr`, нормализовать через `Location\Helper::prepareKladr()`.
- В `Location\Helper::prepareKladr()` добавить обрезку до 13 символов перед `str_pad(..., 13, '0')`.
- В CI workflows для stage/main определять merge‑коммит по двум родителям и брать subject второго родителя; форматировать summary (Effects/Intent).

### Code Touchpoints
- `ImShop::getDeliveryMethods()` — нормализация `$locationCode`.
- `ImShop::createOrder()` — установка `DeliveryLocation` по нормализованному KLADR.
- `ImShop::calculateBasket()` — аналогичная нормализация перед расчётом доставки.
- `Location\Helper::prepareKladr()` — обрезка >13 символов.
- `.github/workflows/{stage,main}-pull-on-commit.yaml` — заголовок из второго родителя merge‑коммита, Markdown‑формат.

### Gotchas
- Не логировать персональные адресные данные; проверять `LocationTable::getByCode()` перед установкой.
- Учитывать возможное отсутствие ключей в `addressData`/payload (использовать null‑coalesce).

### Verification
- Вебхуки: `get-delivery-methods`, `calculate-basket`, `create-order` с payload, где `cityKladr`/`city_kladr`/`kladr` — по очереди; ожидать валидный список доставок и успешное создание заказа.
- Негородские адреса с длинным KLADR (>13) → успешно обрезаются, код локации найден.
- CI: при merge удалённых веток в stage/main — заголовок сообщения = subject последнего коммита ветки.

### Follow-ups
- Покрыть unit‑тестами `prepareKladr()` и парсинг KLADR в `ImShop` (при наличии тестовой обвязки).

