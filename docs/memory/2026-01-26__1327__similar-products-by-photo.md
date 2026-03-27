# Task Memory Card

Date: 2026-01-26
Task Key: 1327
Title: Похожие товары по фото (тестовая версия)

## Context

- Реализован расчёт и вывод «похожих товаров» по изображению: эмбеддинги фото через OpenCLIP, хранение векторов в Qdrant, рекомендации по косинусной близости.
- Данные хранятся в свойствах элемента каталога (SIMILAR_PRODUCTS — JSON, SIMILAR_PRODUCTS_UPDATED — дата расчёта), а не в highload.
- Зависимость: задача 1325 (установка Qdrant).

## Key Decisions

- **Хранение**: свойства инфоблока каталога `SIMILAR_PRODUCTS` (строка, JSON-массив `[{xml_id, distance, score}]`) и `SIMILAR_PRODUCTS_UPDATED` (DateTime). Миграция: `app/local/changes/db/1327/add_similar_by_photo_property.php`.
- **Выборка для пересчёта**: `loadElements()` через `CIBlockElement::GetList()`; фильтр — товары без SIMILAR_PRODUCTS или с SIMILAR_PRODUCTS_UPDATED старше X дней (параметр `similarProductsDaysThreshold`), формат даты для фильтра Bitrix: `Y-m-d H:i:s`.
- **Порог похожести**: параметр `minScore` (по умолчанию 0.58); в рекомендации попадают только товары с score ≥ minScore.
- **Эмбеддинг по частям**: товары разбиваются на чанки (`PRODUCTS_PART_COUNT = 5`); для каждого чанка: вызов Python `embed_batch.py` → upsert в Qdrant → пересчёт рекомендаций и запись SIMILAR_PRODUCTS/SIMILAR_PRODUCTS_UPDATED для каждой позиции чанка (запись на каждом шаге, а не в конце).
- **Запись похожих**: метод `saveSimilarLinks` реализован в классе импорта `App\Import\Base\SimilarProducts` (protected); в `App\Catalog\SimilarProducts` оставлена публичная обёртка для вызова извне.

## Code Touchpoints

- `app/local/changes/db/1327/add_similar_by_photo_property.php` — создание свойств SIMILAR_PRODUCTS, SIMILAR_PRODUCTS_UPDATED.
- `app/local/php_interface/lib/app/Import/Base/SimilarProducts.php` — step1 (loadElements, chunk, embed, upsert, recalc + saveSimilarLinks per chunk), loadElements (фильтр по свойствам), saveSimilarLinks, getDefaultParameters (minScore, similarProductsDaysThreshold, productsPerRun и др.).
- `app/local/php_interface/lib/app/Catalog/SimilarProducts.php` — чтение из свойств (getSimilarProductsXmlIds), getSimilarProductIds, getSimilarArrFilter, saveSimilarLinks (обёртка записи).
- `app/local/cron/calculate_similar_products.php` — запуск импорта SimilarProducts с параметрами (qdrantUrl, pythonBin, embedScript, minScore, similarProductsDaysThreshold и т.д.).
- `system/.dev/scripts/similar_images/python/embed_batch.py` — генерация эмбеддингов (OpenCLIP).
- `app/local/templates/trimiata/include_areas/catalog/similar_products.php` — вывод блока похожих (использует Catalog\SimilarProducts / Helper).
- `app/local/php_interface/lib/app/Catalog/SimilarProductsArrFilter.md` — описание использования arrFilter для похожих (актуально при хранении по XML_ID/JSON).

## Cause–Effect

- **1325 (Qdrant)** → 1327 использует Qdrant для хранения векторов и поиска похожих.
- **Миграция 1327** добавляет свойства → импорт пишет в них, каталог читает при выводе.
- **Фильтр loadElements** (пустое SIMILAR_PRODUCTS или старая SIMILAR_PRODUCTS_UPDATED) → в расчёт попадают только товары, которые ещё не считались или устарели.
- **Чанки + запись после каждого чанка** → меньше пиковой нагрузки по памяти и постепенное обновление данных при падении в середине прогона.

## Gotchas

- Для фильтра по свойству DateTime в Bitrix в GetList используется формат `Y-m-d H:i:s`, не `d.m.Y H:i:s`.
- `array_chunk($items, N, true)` — с `true` сохраняются ключи (xml_id), иначе в чанке нельзя обратиться к `$chunk[$xmlId]`.
- Крон должен иметь доступ к Python (venv), скрипту embed_batch и к Qdrant (например, 127.0.0.1:6333).

## Verification

- Выполнить миграцию 1327 (создание свойств).
- Запустить крон `calculate_similar_products.php` (проверить логи: загрузка элементов, эмбеддинг по частям, upsert, links_recalculated).
- На детальной странице товара с заполненным SIMILAR_PRODUCTS должен отображаться блок похожих товаров.

## Commit summary

Тестовая версия блока «похожие товары по фото»: расчёт по эмбеддингам (OpenCLIP + Qdrant), хранение в свойствах товара, запись после каждой части расчёта. Повышает удержание на детальной за счёт релевантных рекомендаций.

## Follow-ups

- Оценка качества рекомендаций (minScore, topCount) на реальных данных.
- При необходимости — параметр productsPerRun/ограничение по времени для крона.
- Документация по развёртыванию (Python, Qdrant, расписание крона) для эксплуатации.
