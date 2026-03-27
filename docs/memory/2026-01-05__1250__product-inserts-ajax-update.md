# 1250: Обновление блока вставок при смене веса на детальной странице

**Дата:** 2026-01-05  
**Задача:** 1250

## Context

При смене веса (offer) на детальной странице товара обновлялись цена и информация о доставке, но блок с информацией о вставках оставался без изменений. Требовалось реализовать AJAX-обновление блока вставок аналогично блоку доставки.

## Key Decisions

1. **API endpoint для получения HTML вставок** — создан `app/api/product/inserts.php` по аналогии с `app/api/product/delivery.php`
2. **Оптимизация запроса** — получение `PROPERTY_INSERT` напрямую в выборке через `GetNext(false, false)` вместо `GetProperties()`
3. **Упрощенная логика обновления** — замена содержимого контейнера `data-role="product-inserts"` без сложной анимации (в отличие от блока доставки)

## Code Touchpoints

### Backend
- `app/api/product/inserts.php` (новый) — API endpoint для получения HTML вставок по XML_ID оффера
  - Получает оффер из `APP_IBLOCK_CATALOG_SKU` по XML_ID
  - Извлекает `PROPERTY_INSERT_VALUE` напрямую из выборки
  - Генерирует HTML через `TemplateHelper::getInsertsHtml($insert)`

### Frontend
- `app/local/templates/trimiata/js/AppApi.js` — добавлен метод `inserts()` для вызова API
- `app/local/templates/trimiata/js/App.js` — добавлен метод `getProductInserts(productXmlId)`
- `app/local/templates/trimiata/js/AppBasket.js`:
  - Добавлен селектор `detailInserts: '[data-role=product-inserts]'` в `elements`
  - В обработчике `product-change-offer` добавлено обновление блока вставок через AJAX
  - Замена содержимого контейнера при успешном ответе

## Gotchas

- Блок вставок может отсутствовать, если у оффера нет вставок — проверка `insertsContainer` обязательна
- HTML вставок включает заголовок "Вставки" и сам блок `product-inserts`, поэтому извлекаем только содержимое блока
- Использование `GetNext(false, false)` вместо `GetNextElement()` + `GetProperties()` более эффективно (один запрос вместо двух)

## Verification

1. Открыть детальную страницу товара с разными весами (офферами)
2. Кликнуть на элемент с `data-role="product-change-offer"`
3. Проверить, что блок вставок обновляется при смене веса
4. Проверить, что если у нового оффера нет вставок, блок остается пустым или скрывается

## Follow-ups

- При необходимости можно добавить анимацию загрузки аналогично блоку доставки
- Рассмотреть кеширование HTML вставок на клиенте для повторных переключений
