## 1201: Порядок пунктов меню «Подвески» — «Сердце» первым

Context
- Требование: в меню шапки для категории `podveski` подкатегория с кодом `podveski-serdtse` должна отображаться первой.
- Зона кода: `app/local/templates/trimiata/components/app/catalog.section.list/header/result_modifier.php`.

Key Decisions 
- Реализовано переупорядочивание через `usort` только для `CODE==='podveski'`.
- Критерий приоритета: элементы с `CODE==='podveski-serdtse'` всегда первыми; остальные порядок сохраняют относительно друг друга.
- Правка минимальна и выполняется до подготовки изображений/рендеринга.

Code Touchpoints 
- `result_modifier.php`: добавлен блок перед обходом `$arItem['SUBCATEGORIES']`:
  - Проверка `!empty($arItem['CODE']) && $arItem['CODE']==='podveski'`.
  - `usort($arItem['SUBCATEGORIES'], static function($a,$b){ ... })` с приоритетом для `podveski-serdtse`.

Gotchas
- Учитываем отсутствие/тип массива `SUBCATEGORIES` (проверка `is_array`).
- Без побочных эффектов для других категорий.

Verification
- В шапке → Каталог → «Подвески»: подкатегория «Сердце» отображается первой.
- Другие категории не изменили порядок.

Follow-ups
- При появлении доп. приоритетных подкатегорий в «Подвесках» — расширить правило comparator'a.

