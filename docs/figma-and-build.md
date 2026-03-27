## Генерация стилей и сборка верстки (Trimiata)

### Обзор
- Исходники фронта: `app/local/changes/template` (JS/SCSS/assets).
- Сборка: webpack (`webpack.bx_build.js`) → `dist/` → afterbuild (`webpack.afterbuild.js`) копирует в `app/local/templates/trimiata/bundle`.
- Интеграция Figma:
  - Импорт токенов (тексты/цвета/миксины) через PHP‑скрипт `app/local/cron/figma.php` (использует `App\Figma\{Service,Helper,Figma}` и конфиг `template.figma.import`).
  - npm `figma` запускает удалённый импорт по SSH и синхронизирует SCSS‑файлы в `src/styles/template/layout/base/import/`, затем `npm run build`.
  - PoC codegen по node_id (DevMode API) — `system/figma/{pull,codegen,run}` (см. `docs/memory/2025-09-15__figma-auto-codegen-poc.md`).

### Сборка ассетов (webpack)
- Entry:
  - `js/scripts.min` → `src/js/scripts.js` (агрегатор модулей UI).
  - `style` → `src/styles/style.scss` (агрегатор SCSS).
- Структура JS исходников:
  - `src/js/App.js` — главный модуль приложения
  - `src/js/AppApi.js`, `AppStorage.js` — утилиты
  - `src/js/app/` — модули функциональности (`AppBasket.js`, `AppForm.js`, `AppSmartFilter.js`, `AppOrder.js` и т.д.)
  - `src/js/modules/` — вспомогательные модули (`Scrollyeah.js`)
  - `src/js/Router.js` — роутер для lazy loading модулей
- Loaders/плагины: `babel-loader`, `sass-loader`, `postcss-preset-env`, `MiniCssExtractPlugin`, `CssMinimizerPlugin`, `TerserPlugin`.
- Output: `dist/` (js/css/maps). Afterbuild переносит `dist/` и `src/img/` → `templates/trimiata/bundle/`.
- **Важно**: не редактировать файлы в `dist/` или `bundle/` напрямую — они генерируются автоматически. Все правки делаются в `src/js/`.
- Скрипты `package.json`:
  - `build`: `webpack --config webpack.bx_build.js`.
  - `postbuild`: `node webpack.afterbuild.js` (кроссплатформенно; без sh).
  - `figma`: `wsl bash system/.dev/scripts/figma_import/script.sh && npm run build`.

### Структура SCSS
- Корневой: `src/styles/style.scss` → assets (`bootstrap`, `tingle`, `fancybox`, `swiper`, `scrollyeah`) → `template/layout`.
- `template/layout.scss` → `layout/base.scss`, `blocks.scss`, `pages.scss`, `other.scss`.
- База (`layout/base.scss`):
  - Импорт цветов из Figma: `base/import/colors.scss`.
  - Базовые части: `functions`, `variables`, `placeholders`, `mixins`, `colors`, `fonts`, `reset`.
  - Включения сгенерированных из Figma миксинов: `base/import/import.scss` (texts, headers_small, headers_large, include).
  - Общие стили: `base/common`.
- Блоки/страницы: `template/layout/blocks/**/*.scss`, `template/layout/pages/**/*.scss` — атомы и макеты (header/footer/menu, product/catalog/auth и т.д.).

### Откуда берутся include: _color, _style, _transition, _background, _borderColor
- Примеры использования (ускоряют вёрстку по токенам):
  - `@include _color('primary/tints/600');`
  - `@include _style('text-m/font-normal');`
  - `@include _transition();`
  - `@include _background('primary/tints/600');`
  - `@include _borderColor('black/tints/50');`
- Определения миксинов:
  - `_transition()` — `src/styles/template/layout/base/mixins.scss`.
  - `_style($style)`, `_color($color, $addHover, $iconHover)`, `_background($color)`, `_borderColor($color)` — генерируются в `src/styles/template/layout/base/import/include.scss`.
- Источник генерации:
  - `npm run figma` → `system/.dev/scripts/figma_import/script.sh` запускает в контейнере `php app/local/cron/figma.php` и синхронизирует файлы в `src/styles/template/layout/base/import/`.
  - PHP‑скрипт читает конфиг `template.figma.import` из `app/local/php_interface/config/template.php` и через `App\Figma\{Figma,Service,Helper}` формирует:
    - `colors.scss` — SASS‑переменные `$primary-tints-600` и CSS переменные `:root { --primary-tints-600: ... }`.
    - `texts.scss`, `headers_*` — наборы миксинов шрифтов (_style маппинг → конкретные миксины, основываясь на DevMode API).
    - `include.scss` — «универсальные» миксины `_style/_color/_background/_borderColor` с логикой hover из `template.figma.import.develop.mixins_include.mixins._color.additional`.
- Почему это важно:
  - Верстальщик пишет декларативно по токенам Figma (строковые значения), а миксины преобразуют их в CSS через CSS‑переменные `--*` и SCSS‑миксины — это ускоряет и унифицирует вёрстку.

Note: преобразование `'primary/tints/600'` → `primary-tints-600` выполняет `@function get-variable-name()` в `base/functions.scss` (замена `/`→`-`, удаление `--`, `$`, `%`).

### Где используются include и как подключены
- Поиск по проекту показывает массовое использование (`layout/other.scss` и др.): `_transition`, `_color`, `_background`, `_borderColor`.
- Подключение обеспечено цепочкой импорта: `style.scss` → `template/layout.scss` → `layout/base.scss` → `base/import/{colors.scss,import.scss}` → `include.scss`.
- Причинно‑следственная связь:
  - Figma → `npm run figma` → генерируются токены/миксины → импорт в базовый слой SCSS → использование миксинов в блоках/страницах → единый визуальный язык и быстрые правки.

## Глубокая связка с PHP‑генератором (figma.php)

### Конфигурация: какие узлы Figma подтягиваются и куда пишем SCSS
- Конфиг берётся из `Ctx::config('template.figma.import')` в `app/local/php_interface/config/template.php`.
- В нём описаны `texts` (узел с текстовыми стилями, узлы `headers`), `colors.items` (пакеты цветов), и `develop.mixins_include.file` с расширениями для hover:
```40:120:app/local/php_interface/config/template.php
'texts' => [ 'text' => ['node_id' => '15099-208772','file' => '/local/changes/template/src/styles/template/layout/base/import/texts.scss'], 'headers' => [ 'large' => ['node_id' => '4335-11250','file' => '/local/changes/template/src/styles/template/layout/base/import/headers_large.scss'], 'small' => ['node_id' => '4883-911','file' => '/local/changes/template/src/styles/template/layout/base/import/headers_small.scss'] ] ],
'colors' => [ 'items' => [ 'tints' => ['node_id' => '15146-45060'], 'black' => ['node_id' => '3319-9667'], 'primary' => ['node_id' => '4995-9635'] ], 'file' => '/local/changes/template/src/styles/template/layout/base/import/colors.scss' ],
'develop' => [ 'mixins_include' => [ 'file' => '/local/changes/template/src/styles/template/layout/base/import/include.scss', 'mixins' => [ '_color' => [ 'additional' => function () { /* динамическая логика hover по цветам */ } ] ] ] ]
```

### Генерация файлов из Figma: app/local/cron/figma.php (схема)
- Тексты (`texts.scss`) и заголовки (`headers_*.scss`):
```74:101:app/local/cron/figma.php
$_nodeId = $arr['node_id'];
if (($res = $service->fileNodes($_nodeId)) && $res['success']) {
  $nodes = $res['data']['nodes'][Helper::nodeIdToKey($_nodeId)];
  if ($nodes && $styles = $nodes['styles']) {
    foreach ($styles as $nodeId => $style) {
      if (stripos($style['name'], "text-") === 0) {
        $nodeId = Helper::nodeIdToRequest($nodeId);
        if (($resStyle = $service->fileNodes($nodeId)) && $resStyle['success']) {
          if ($mixin = Helper::getTextMixin($resStyle, $nodeId)) {
            $styleNames[] = Helper::getStyleName($resStyle, $nodeId);
            $result .= $mixin;
          }
        }
      }
    }
  }
}
```
- Цвета (`colors.scss`):
```132:170:app/local/cron/figma.php
foreach ($config['colors']['items'] as $type => $ar) {
  if (($res = $service->fileNodes($ar['node_id'])) && $res['success']) {
    $nodes = $res['data']['nodes'][Helper::nodeIdToKey($ar['node_id'])];
    if ($nodes && $styles = $nodes['styles']) {
      foreach ($styles as $nodeId => $style) {
        // фильтр подходящих стилей по префиксам имени
        $nodeId = Helper::nodeIdToRequest($nodeId);
        if (($resStyle = $service->fileNodes($nodeId)) && $resStyle['success']) {
          if (($data = Helper::getColorVariable($resStyle, $nodeId)) && $data['variable'] && $data['color']) {
            $styleName = Helper::getStyleName($resStyle, $nodeId);
            if (in_array($styleName, $colorNames)) continue;
            $colorNames[] = $styleName;
            $result .= '$' . $data['variable'] . ': ' . $data['color'] . ";\n";
            if (!strlen($variables)) $variables = ":root {\n";
            $variables .= '--' . $data['variable'] . ': ' . $data['color'] . ";\n";
          }
        }
      }
    }
  }
}
```
- Универсальные include‑миксины (`include.scss`) на базе собранных стилей/цветов:
```173:246:app/local/cron/figma.php
// _style: собирает if/else карту по именам текстовых стилей
// _color/_background/_borderColor: универсальные миксины c поддержкой строковых токенов
// Дополнение hover‑логики подтягивается из config через Helper::checkMixinAdditional(...)
```

### Как Helper трансформирует данные Figma → SCSS
- Текстовые стили → набор миксинов (`@include font-weight/fs-px/lh-px/ls-px/uppercase`):
```83:103:app/local/php_interface/lib/app/Figma/Helper.php
public static function getTextMixin($res, $nodeId) {
  $style = $res['data']['nodes'][Helper::nodeIdToKey($nodeId)]['document'];
  $mixin = ['name' => $style['name'], 'content' => self::getTextStyle($style['style'])];
  if ($mixin['content']) { $mixinName = str_ireplace('/', '-', $mixin['name']);
    $result .= "@mixin $mixinName {\n"; foreach ($mixin['content'] as $item) { $result .= "   $item"; } $result .= "}\n\n"; }
}
```
- Разбор текстового стиля:
```184:209:app/local/php_interface/lib/app/Figma/Helper.php
if ($fontWeight = $style['fontStyle']) { /* light/normal/bold → 300/400/500 */ $result[] = "@include font-weight($fontWeight);\n"; }
if ($fontSize = $style['fontSize'])   { $result[] = "@include fs-px($fontSize);\n"; }
if ($lineHeight = $style['lineHeightPx']) { $result[] = "@include lh-px($lineHeight);\n"; }
if ($letterSpacing = $style['letterSpacing']) { $result[] = "@include ls-px($letterSpacing);\n"; }
if ($style['textCase'] == 'UPPER') { $result[] = "@include uppercase;\n"; }
```
- Цветовые переменные и CSS vars:
```128:141:app/local/php_interface/lib/app/Figma/Helper.php
$style = $res['data']['nodes'][Helper::nodeIdToKey($nodeId)]['document'];
if ($style && ($fill = $style['fills'][0]) && $color = self::getFillColor($fill, $nodeId)) {
  $result['color'] = $color; // hex/rgba
  $result['variable'] = self::nameToVariable($style['name']); // 'primary/tints/600' → 'primary-tints-600'
}
```

### Почему `@include _style/_color/_background/_borderColor` ускоряют вёрстку
- Разработчик использует строковые токены из Figma (`'primary/tints/600'`, `'text-m/font-normal'`), не вспоминая числовые значения.
- Миксины маппят токены → CSS vars/SCSS миксины, добавляют стандартные состояния (hover/иконки) по единой логике.
- Изменение палитры/типографики в Figma → `npm run figma` → регенерация include‑миксинов без ручного поиска/замены в стилях.

### Практика: добавить новый токен/стиль
1) В `app/local/php_interface/config/template.php` добавить/обновить `node_id` нужного узла (texts/headers/colors).
2) Запустить `npm run figma` (удалённый cron + rsync), затем `npm run build`.
3) Использовать в SCSS: `@include _color('<group>/<name>');`, `@include _style('<family>/<variant>');`.

## Быстрый старт (онбординг)
- Обновить токены Figma → `npm run figma` → `npm run build && npm run postbuild` → проверить `templates/trimiata/bundle`.
- Использовать миксины по токенам: `@include _style('text-m/font-normal')`, `@include _color('primary/tints/600')`, `@include _transition()`.
- Где править:
  - SCSS блоков/страниц: `src/styles/template/layout/{blocks,pages}/`.
  - База (миксины/функции/переменные): `src/styles/template/layout/base/*`:
    - `functions.scss` → `get-variable-name()` (преобразует `'primary/tints/600'` → `primary-tints-600`).
    - `mixins.scss` → базовые миксины (`_transition`, типографика `fs-px/lh-px/ls-px`, `font-weight`, `uppercase`).
    - `variables.scss` → адаптация глобальных переменных и alias цветовых токенов (напр., `$color__gold: $primary-tints-600`).
    - `colors.scss` → классы-утилиты `color/bg/border-*` на базе переменных.
  - Figma конфиг: `app/local/php_interface/config/template.php` (`template.figma.import`).

## Частые ошибки и отладка
- `npm run figma` падает: проверьте `app/.env` (SSH/compose vars) и доступ к удалённому контейнеру; скрипт: `system/.dev/scripts/figma_import/script.sh`.
- Нет миксинов `_style/_color`: проверьте, что `base/import/include.scss` сгенерирован и импортируется через `base/import/import.scss`.
- Нет цветов/текстов: убедитесь, что `colors.scss`/`texts.scss` существуют и подключены в `base.scss` (и что Figma узлы указаны корректно в конфиге).


### Импорт Figma (токены → SCSS)
- Конфиг `Ctx::config('template.figma.import')` описан в `app/local/php_interface/config/template.php`:
  - `texts` → node_id узла и файл для записи (`texts.scss`), `headers_large.scss`, `headers_small.scss`.
  - `colors` → список `items` (node_id палитр), файл `colors.scss` (переменные `$...` и CSS vars `:root`).
  - `develop.mixins_include.file` → `include.scss` + `mixins._color.additional` — функция, дописывающая правила hover по цветам (генерируется динамически).
- Скрипт `app/local/cron/figma.php`:
  - `Figma::fileNodes(nodeId)` → получает JSON ноды/стилей; `Helper` строит SCSS миксины шрифтов (`@mixin fs-px, lh-px, font-weight, ls-px, uppercase`) и цветовые переменные.
  - Пишет файлы: `texts.scss`, `headers_*.scss`, `colors.scss`, `include.scss` в `src/styles/template/layout/base/import/`.
- Запуск локально (через удалённый контейнер): `npm run figma`:
  - `system/.dev/scripts/figma_import/script.sh` берёт `.env`, подключается по SSH, выполняет в контейнере `php /var/www/app/local/cron/figma.php`.
  - После генерации делает `rsync` SCSS на локальную машину и запускает `npm run build`.

### Причинно‑следственные связи
- Импорт токенов Figma синхронизирует дизайн‑систему: цвета/текстовые стили → SCSS миксины и CSS vars → вся вёрстка использует единые миксины/переменные.
- Afterbuild копирует собранные ассеты в шаблон Bitrix — единая точка правды для продакшн‑темы (`bundle/`).
- PoC codegen закрывает разрыв «макет→вёрстка»: базовая геометрия/стили генерируются автоматически, дальше интегрируются в шаблоны компонентов.

### Процедура (практика)
1) Изменили токены в Figma → `npm run figma` (генерирует `colors.scss`, `texts.scss`, headers, include) → `npm run build` → ассеты в `bundle/`.
2) Разработка блоков/страниц: правим SCSS в `template/layout/*`, JS в `src/js/*`, запускаем `build+postbuild`.
3) Codegen (PoC): по `node_id` делаем `figma:node` в `demo` или `apply`, затем добавляем partials в layout/blocks/pages и подключаем к компонентам.

### Примечания/риски
- `npm run figma` зависит от доступа к удалённому контейнеру и переменным `.env` (SSH, COMPOSE_PROJECT_NAME). На чистом локале без докера не сработает.
- В `Service` есть OAuth‑креды для Figma; реальных секретов в VCS быть не должно — используйте `.env`.
- Локальная сборка кроссплатформенная: postbuild — на Node; shell‑скрипт остался только для orchestration figma‑pull на удалённом контейнере.


