## 1181: Изображения превью видео — внешняя генерация и адресация

### Context
- Задача 1142 внедряла тестовую локальную генерацию превью из видео (Python/OpenCV → ffmpeg). Теперь генерация перенесена во внешнюю систему.
- Цель: убрать собственную генерацию, перейти на детерминированные адреса готовых превью и использовать их в карточке товара.

### Key Decisions
- Удалить python/ffmpeg/cron логику (локальная генерация отключена).
- Именование: `<videoId>_preview.jpeg`, где `<videoId>` — часть имени файла видео до первого символа `_` (пример: `123_v1.mp4` → `123_preview.jpeg`).
- Размеры превью: как `VIDEO_SIZES` + `smallest=100` (итог: `1080/700/500/260/100`).
- URL‑схема: `https://media.trimiata.ru/RESIZED/PREVIEW/{size}x{size}/{id}_preview.jpeg`.
- Локация хелперов: рядом с фото товара — `App\Catalog\Helper` (`getVideoPreviewFileName`, `getProductVideoPreviewImages`).
- Использование на детальной: постер `poster` и миниатюры берутся из `VIDEO_PREVIEW` с фолбэком на `PICTURES`.

### Code Touchpoints
- `app/local/php_interface/lib/app/Catalog/Helper.php`
  - `getVideoPreviewFileName(string)`
  - `getProductVideoPreviewImages(array, bool)`
  - Дополнение `getProductAdditionalData()` → `$product['VIDEO_PREVIEW']`
- `app/local/php_interface/lib/app/Template/Helper.php`
  - `VIDEO_SIZES` и `VIDEO_PREVIEW_SIZES` (как reference размеров)
  - Удалены прежние методы превью (оставлены комментарии‑переадресации)
- `app/local/templates/trimiata/components/app/catalog.element/main/result_modifier.php`
  - Подстановка `VIDEO_PREVIEW.detail[0]` → `poster`, `VIDEO_PREVIEW.preview[0]` → thumbnail; fallback на фото товара
- Удалено: `app/local/cron/generate_video_thumbs.py`, `app/local/cron/import_catalog_images.php`

### Gotchas
- Внешняя система должна стабильно выпускать превью в `RESIZED/PREVIEW/{size}x{size}/` по имени `_preview.jpeg` — иначе 404.
- Несоответствие имени видео правилу (отсутствует `_`) приведёт к иному `<videoId>` (целиком имя без расширения).

### Verification
- Открыть карточку товара с видео: проверить `poster` и миниатюры в DevTools, отсутствие 404; при отсутствии превью — корректный фолбэк на фото.
- Проверить URL‑схему на несколько размеров: `1080/700/500/260/100`.

### Follow-ups
- Опционально: кешировать наличие превью (HEAD/200) при сборке/импорте данных, чтобы исключать пустые ссылки.
- Документировать SLA внешнего ресайзера и ретрай/дефолты на уровне CDN.

