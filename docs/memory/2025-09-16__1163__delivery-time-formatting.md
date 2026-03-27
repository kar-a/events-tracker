## 1163 — Единый формат вывода сроков доставки (DPD как EMS)

Context
- На детальной странице (напр., `https://trimiata.ru/catalog/chasy/chasy-naruchnye-00001291/`) блок `data-role="detail-product-delivery"` показывал DPD как: «дн. 4 (DPD OPTIMUM)», в то время как EMS — «от 5 до 7 дней».
- Требование: унифицировать вид для DPD до человекочитаемого текста по правилам EMS и без лишних одноразовых переменных в коде.

Key Decisions
- Формат: «от N до M дней», «до N дней» или «N день/дня/дней». Добавляется в одну строку с ценой.
- Ветвление только для `DELIVERY_TYPE_DPD_PICKUP` и `DELIVERY_TYPE_DPD_COURIER`; прочее поведение без изменений.
- Плюрализация «дней» вынесена в `formatDaysWord()`, сборка строки — в `formatDeliveryTime()`.
- Избегаем одноразовых переменных: сразу приводим `FROM/TO` к `(int)` в аргументах вызова; фолбэк из `TIME.DESCRIPTION` без промежуточных `$days`.

Code Touchpoints
- `app/local/php_interface/lib/app/Order/External/Main.php` → `getDeliveryMethods()` (ветка DPD) + приватные `formatDaysWord()`/`formatDeliveryTime()`.
- Шаблон отображения: `app/local/templates/trimiata/components/app/catalog.element.detail.delivery/main/template.php` (использует `DESCRIPTION_READABLE`).

Gotchas
- У разных служб Bitrix `PeriodFrom/To` могут быть пустыми — предусмотрен фолбэк на числовой паттерн в `TIME.DESCRIPTION`.
- EMS/курьер/самовывоз магазина: логика срока не менялась; проверять регрессии «сегодня/завтра/в наличии».

Verification
- DPD: FROM=4, TO=7 → «… от 4 до 7 дней»; FROM=0, TO=5 → «… до 5 дней»; FROM=4, TO=4 → «… 4 дня».
- Если `DESCRIPTION` содержит «дн. 4» и нет FROM/TO → «… 4 дня».
- Вёрстка блока не менялась, только текст.

Follow-ups
- При необходимости локализовать слова («день/дня/дней») вынести в общий хелпер локализации.

