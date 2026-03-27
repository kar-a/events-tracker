Date: 2025-09-08
Task: 1152 — Не влезающие названия фильтров в две строки

Context
- В каталоге длинные заголовки свойств фильтра (`.catalog_sidebar__title span`) не переносились из‑за `white-space: nowrap` и `text-overflow: ellipsis`.
- Требование: переносить заголовок на вторую строку, сохранить естественный `line-height` и визуальный баланс со стрелкой.

Key Decisions
- Удалить `nowrap/ellipsis` и разрешить перенос слов: `white-space: normal; word-break: break-word; overflow-wrap: anywhere;`.
- «Опустить» текст на baseline: `bottom: 0` вместо `-1px`.
- Сохранить общий `line-height: 1.8rem` заголовка — выглядит естественно на 1–2 строках.

Code Touchpoints
- `app/local/changes/template/src/styles/template/layout/pages/catalog/catalog-sidebar/catalog-sidebar.scss`
  - Секция `.catalog_sidebar__title span` — правки переноса и позиционирования.

Gotchas
- На узких экранах перенос может занять 2 строки — убедиться, что стрелка не перекрывает текст (используем `justify-content: space-between`).
- Семантика текста (в верхнем регистре) сохраняется через существующие миксины.

Verification
- Длинные названия (более 20–25 символов) переносятся на 2 строки без обрезки и с правильной межстрочной высотой.
- На мобильных/десктопе верстка стабильна, стрелка выровнена.

Follow-ups
- При необходимости ограничить максимум двумя строками с `line-clamp` (пока не требуется ТЗ).

