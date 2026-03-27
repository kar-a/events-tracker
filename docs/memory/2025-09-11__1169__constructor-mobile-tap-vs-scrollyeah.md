## 1169: Конструктор — мобильный tap конфликтует со Scrollyeah

Context
- На карточке товара при активном горизонтальном скролле (Scrollyeah) тапы по значениям конструктора (напр., диаметр 0.55) не срабатывали.
- Корень — агрессивное подавление клика и preventDefault в событиях pointerDown/Up.

Decisions
- Убрать preventDefault в pointerDown/Up.
- Повысить порог drag до 10px (устойчивость к дрейфу пальца).
- Подавлять клик только если действительно был drag (`_suppressClick` после перемещения).

Touchpoints
- `app/local/changes/template/src/js/modules/Scrollyeah.js`
- `.github/commit-summary.txt`, `docs/CHANGELOG.md`

Verification
- Мобильный: короткий тап по значению параметра активирует ссылку.
- Горизонтальный свайп — скроллит; клик подавляется, позиция сохраняется.

