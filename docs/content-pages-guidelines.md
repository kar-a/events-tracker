## Контентные страницы: скелет, правила и принципы (для ИИ)

### Инварианты (обязательные правила)
- Нейминг классов: только snake_case; дефисы `-` запрещены (исключение легаси: `.page-overview`).
- Единицы измерения: по умолчанию `rem`; `px` допустимы для границ/теней.
- Токены дизайна: цвета/фон/бордер/типографика только через `@include _color/_background/_borderColor/_style/_transition`.
- Локальные ассеты: никакого `https://c.animaapp.com`; SVG/PNG/WEBP храним в `pages/<slug>/src/{svg,img}` или инлайн‑SVG.
- Генерация из Figma: файлы `template/layout/base/import/*` не редактируем вручную.
- Каркас страницы: используем `.page(.page_overview)`, `.page-overview`, `.page__article`, `.page__paragraph*`, `.page__article_title{,_big}`.

### Цель
Единый способ быстро создавать/редактировать контентные страницы (about/exchange/warranty/loyalty), используя общий каркас, токены Figma и переиспользуемые блоки.

### Где смотреть (реальные примеры)
- `app/company/index.php` + SCSS: `template/layout/pages/company/company.scss`, `blocks/common/page/{page.scss,page-overview.scss}`, `blocks/accent-card/*.scss`.
- `app/exchange/index.php` + SCSS: `blocks/common/page/page-banner.scss`, `blocks/common/page/{page.scss,page-overview.scss}`.
- `app/loyalty/index.php` + Scrollyeah для горизонтальных рядов: `src/js/modules/Scrollyeah.js`, `styles/assets/scrollyeah.scss`.
- Общая документация: `docs/figma-and-build.md` (пайплайн Figma→SCSS→Bundle, токены, include‑миксины).

### Каркас страницы (обязательный)
- Разметка:
  - `.container > .section > .page(.page_overview) > [aside].page_sidebar__list + .page-overview > .page__article*`
  - Текст и списки — только внутри `.page__article`, абзацы — `.page__paragraph{_*}`.
  - Заголовки — `.page__article_title{,_big}`.
- Стили: `blocks/common/page/{page.scss,page-overview.scss}` обеспечивают грид/фон/отступы/типографику.

### Причинно‑следственные связи (TL;DR)
- Figma → `npm run figma` → PHP `figma.php` генерирует SCSS (`colors.scss`, `texts.scss`, `headers_*.scss`, `include.scss`).
- SCSS база импортирует генерацию → миксины становятся доступны в стилях страниц/блоков.
- Верстка страниц использует токены Figma через миксины; размеры в `rem`.
- Ассеты из макета импортируются локально → HTML с относительными путями `./svg/*`, `./img/*` (SVG желательно inline).
- Webpack собирает → afterbuild копирует в `templates/trimiata/bundle` → Bitrix использует bundle.
### Нейминг (смысловые правила)
- Каркас: `.page*`, `.page-overview`, `.page__article`, `.page__paragraph*` — не менять.
- Тематические блоки именовать по смыслу страницы/секции:
  - `company__*`, `exchange_page_banner*`, `warranty_*`, `loyalty_*` и т.п.
  - БЭМ: `block__element`, модификаторы — суффиксами (`_pb-small`, `_padding_small`, `__icon`, `__title`).

#### Обязательное правило нейминга классов
- Только snake_case: в именах SCSS/CSS‑классов не допускается `-` (дефис). Все дефисы заменяем на `_`.
- Примеры (плохо → хорошо):
  - `.page-banner` → `.page_banner`
  - `.background-logo` → `.background_logo`
  - `.menu-item_active` → `.menu_item_active`
- Легаси‑исключения (не создавать новые, не переименовывать без плана миграции):
  - `.page-overview` и производные — используются каркасом старых страниц. Для новых блоков/страниц применяем только `_`.

### Структура файлов SCSS
- База: `template/layout/base/*` (functions/mixins/variables/colors/reset/fonts/import) — подключается в `base.scss`.
- Каркас страниц: `template/layout/blocks/common/page/*`.
- Повторно используемые блоки: `template/layout/blocks/<feature>/*.scss` (напр., `accent-card`).
- Страницы: `template/layout/pages/<slug>/*.scss` (напр., `pages/company/*`).
- Генерация из Figma: `template/layout/base/import/*` — не редактировать вручную.

### Токены Figma и миксины (как писать стили)
- Цвет/фон/бордер:
  - `@include _color('primary/tints/600');`
  - `@include _background('black/tints/50');`
  - `@include _borderColor('black/tints/100');`
- Типографика:
  - `@include _style('text-m/font-normal');`
- Переходы:
  - `@include _transition();`
- Преобразование `'group/name/value'` → `group-name-value` обеспечивает `get-variable-name()`.
- Источник миксинов: `base/import/include.scss`, генерируется `npm run figma` (`app/local/cron/figma.php`).

### Единицы измерения (rem вместо px)
- По умолчанию используем `rem` для размеров (типографика, отступы, размеры блоков/иконок), исходя из базового `$rem: 16` (см. `base/variables.scss`).
- Исключения (можно оставить px): тонкие границы (`border-width`), тени (`box-shadow`) и случаи, где привязка к физическим пикселям критична.
- Для удобства используйте имеющиеся миксины (`fs-px`, `lh-px`, `ls-px`) или конвертируйте вручную: 16px → 1rem, 24px → 1.5rem, 72px → 4.5rem и т.д.

### SVG‑иконки (inline)
- Не подключаем внешние SVG по URL; иконки инлайн‑вставкой `<svg>` в HTML/шаблон.
- Преимущества: управление цветом через `currentColor`/CSS, отсутствие сетевых задержек, стабильность.
- Правило: любая иконка из макета (Figma/Anima) должна быть локализована и встроена inline. Допускается `use` со спрайтом в шаблоне, если спрайт локальный.
- Процедура замены ссылок:
  1) Собрать все внешние `*.svg` из исходного HTML.
  2) Сохранить файлы в `app/local/changes/template/pages/<slug>/src/svg/`.
  3) Заменить `src="https://.../*.svg"` на `src="./svg/<name>.svg"` (или сразу вставить inline `<svg>` содержимое файла).
  4) При переносе в Bitrix‑шаблон — предпочесть inline `<svg>` вместо `<img>`.

### Растровые изображения (png/jpg/webp)
- Не ссылаемся на внешние CDN (Anima и пр.). Все изображения храним локально в папке страницы `pages/<slug>/src/img/`.
- Пути в HTML относительные: `./img/<name>.<ext>`.
- При переносе в Bitrix‑шаблон — используем ассет‑пайплайн (webpack) и итоговый `bundle/img`.
- Допускается оптимизация изображений (squoosh, imagemin) на этапе сборки; исходники не перезаписываем.

### Горизонтальные ленты (Scrollyeah)
- Разметка: контейнер `.scrollyeah` с дочерними элементами ряда.
- Инициализация: авто по классу (см. `Scrollyeah.initAll()`), опции через `data-*` (`data-disable-if-fit`, `data-shadows`, `data-center-if-fit`).
- Стили: `styles/assets/scrollyeah.scss`.

### Скелет (готовые заготовки)
- HTML: `app/local/changes/template/agent/template/content-page.html` (переименуйте префикс `xpage_`).
- SCSS: `app/local/changes/template/agent/template/content-page.scss` (также замените `xpage_`).

### Процедура (ИИ и разработчик)
1) Скопируйте HTML/SCSS скелеты, замените префикс на slug страницы.
2) Разметьте контент внутри `.page__article` и тематических блоков.
3) В SCSS используйте только токены Figma через `_style/_color/_background/_borderColor/_transition`.
4) При горизонтальных рядах — оборачивайте в `.scrollyeah`.
5) Соберите фронт: `npm run build && npm run postbuild` (или `npm run figma` при изменении токенов).

#### Перенос замен изображений из draft (index.html) в итоговый файл (company.about.html)
- Поиск: найдите во временном `index.html` все ссылки на внешние ресурсы (`https://c.animaapp.com/...`).
- Импорт: скачайте эти файлы локально в `pages/<slug>/src/svg/` и `pages/<slug>/src/img/`.
- Замена: в итоговом `company.about.html` заменяйте внешние пути на относительные `./svg/...` и `./img/...` там, где используются те же изображения.
- Если во втором файле нет внешних ссылок — правки не требуются.

### Чек‑лист QA
- Desktop/tablet/mobile — грид/отступы, видимость баннеров и акцент‑карт.
- Типографика и цвета соответствуют Figma‑токенам.
- Горизонтальные ряды — корректная прокрутка/инерция, клики не подавляются без драга.
- Без «магических чисел»: все цвета/шрифты через include‑миксины.

### Quality gates / Предотвращение регрессий
- Нейминг: в PR отклонять классы с `-` (кроме легаси `.page-overview`). Автолинт: grep по `class="[^"]*-`.
- Внешние ресурсы: блокировать `https://c.animaapp.com/` и иные CDN в `pages/*/src/*.html`.
- Токены: запрещены «жёсткие» HEX/px в новых стилях — использовать `@include _color/_style` и `rem`.
- Сборка: после правок запускать `npm run build && npm run postbuild`; проверять, что `bundle/` обновился.
- Вёрстка страниц через скелет: новые страницы должны начинаться с `agent/template/content-page.*` (копия с заменой префикса).


