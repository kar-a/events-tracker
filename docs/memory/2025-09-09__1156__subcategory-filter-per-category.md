Task: 1156 — Подвид фильтрует только свою категорию

Context →
- Множественный выбор категорий и подвидов: `/catalog/{catA-i-catB-...}/{subA-i-subB-...}/`.
- Ранее подвиды могли сужать все выбранные категории.

Decision →
- Логику ограничили в `app:catalog.full::prepareDataSubcategory()` на уровне фильтра Bitrix:
  - Строится OR по категориям.
  - Для категории, у которой есть выбранные подвид(ы), добавляется AND(`=PROPERTY_CATEGORY` & `=PROPERTY_SUBCATEGORY in [...])`.
  - Остальные категории в OR остаются без дополнений (только `=PROPERTY_CATEGORY`).

Effects →
- Подвиды влияют только на связанные категории; релевантность результатов выше.
- URL/клиент/редиректы не менялись (сборка базового пути — через `Catalog\Helper::getCategoriesUrl`).

Files →
- `app/local/components/app/catalog.full/class.php` (prepareDataSubcategory)

QA →
- Пример: `/catalog/braslety-i-koltsa-i-sergi-i-tsepi-i-chasy/pusety-i-sergi/` →
  - `sergi` фильтруется как AND(sergi & [pusety,sergi]),
  - остальные категории — только по своей категории.


