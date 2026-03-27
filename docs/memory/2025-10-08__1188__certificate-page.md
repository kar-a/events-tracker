## 1188: Страница сертификатов

Дата: 2025-10-08

### Context
- Требование: сверстать новую страницу сертификатов, улучшить UX/валидацию формы, упростить ввод «другая сумма», показывать успешную отправку в попапе (desktop: Bootstrap Modal; mobile: CupertinoPane) вместо замены формы.

### Key Decisions
- Универсальная автокоррекция суммы по атрибутам: `data-autocorrection="Y"`, `data-min-value`, `data-step`.
- Для поля с кодом `ANOTHER_SUM` параметры выставляются сервером в `result_modifier.php` (MIN=3000, STEP=100).
- Успех отправки: не перерисовывать форму — показывать модальное окно; форму очищать и переинициализировать.
- Валидация radio: `required` и обработка jQuery Validation; сообщение об ошибке выводится как для обычных полей.

### Code Touchpoints
- `app/certificates/index.php` — страница сертификатов (галерея/форма, правила через AJAX aside).
- `app/api/modal/certificates-rules.php` — контент правил в модали.
- `app/local/templates/trimiata/components/bitrix/form.result.new/inline/result_modifier.php`
  - `switch ($code)`: `case 'ANOTHER_SUM'` → `AUTOCORRECTION='Y'`, `MIN_VALUE=3000`, `STEP=100`.
- `app/local/templates/trimiata/components/bitrix/form.result.new/inline/template.php`
  - Для текстовых полей вывод `data-*` атрибутов автокоррекции.
- `app/local/templates/trimiata/js/AppForm.js`
  - Универсальная автокоррекция по `data-*` с дебаунсом 500мс, округление вверх до кратности шага, минимум включительно.
  - Success → `showSuccessPopup(html)` (Bootstrap Modal/CupertinoPane) + `resetFormValues()` (очистка инпутов/радио/чекбоксов/селектов и классов).
  - Тоггл зависимых полей: при `data-show-question`/`data-hide-question` переключается `required` и очищается значение.

### Gotchas
- Не выполнять перерисовку формы при success, иначе теряются анимации/фокус модали.
- Для мобильного варианта использовать существующий `App.initCoopertinoPane()`; добавлять контейнер `[data-role=modal-content]`.
- Автокоррекция должна быть параметризуемой, без хардкода кода поля.

### Verification
- Ввод "2999" в поле «другая сумма» → через ~0.5с становится `3000`.
- Ввод "3401" → `3500`; поле остаётся валидным.
- Не выбран `radio` при `required` → отображается ошибка; после выбора ошибка исчезает.
- После отправки: всплывает попап с `SUCCESS_TEXT`, форма очищена (все значения/checked/классы), повторная отправка возможна.
- На мобильном success открывается в CupertinoPane; на десктопе — Bootstrap Modal.

### Follow-ups
- Единый компонент попапов успеха для форм (`AppForm`), параметр для заголовка/кнопок.
- UI/стили для модали успеха (иконка, кнопка «Закрыть/Вернуться»).

