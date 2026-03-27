# Task Memory Card

Date: 2025-09-19
Task Key: build-figma
Title: Пайплайн генерации стилей и верстки (webpack + Figma импорт + codegen PoC)

## Context
- Фронтенд живет в `app/local/changes/template`, продакшн‑ассеты в `app/local/templates/trimiata/bundle`.
- Импорт Figma токенов запускается через `npm run figma` (удаленный php‑скрипт + rsync), после чего выполняется билд.
- PoC codegen по `node_id` позволяет генерировать HTML/SCSS из макета (DevMode API) — демо/применение.

## Key Decisions
- Afterbuild на Node (`webpack.afterbuild.js`) — кроссплатформенно (Windows/CI), без sh.
- Figma импорт пишется в `src/styles/template/layout/base/import/*` и включается через `base/import/import.scss`.
- Цветовые миксины дополняются динамической логикой hover из конфига `template.figma.import.develop.mixins_include.mixins._color.additional`.

## Code Touchpoints
- Webpack: `app/local/changes/template/webpack.bx_build.js`, `webpack.afterbuild.js`, `package.json` (scripts: build/postbuild/figma).
- SCSS структура: `src/styles/style.scss` → `template/layout.scss` → `layout/{base,blocks,pages,other}.scss`.
- Figma PHP: `app/local/cron/figma.php`, `App\Figma\{Service,Helper,Figma}`, конфиг `app/local/php_interface/config/template.php` (`template.figma.import`).
- Figma PoC: `system/figma/{pull,codegen,run}`; команды `figma:node`/`figma:apply` (описаны в памяти 2025-09-15).
- Orchestration: `system/.dev/scripts/figma_import/script.sh` (ssh → docker exec php cron → rsync SCSS → локальный build).

## Gotchas
- Зависимость `npm run figma` от удаленного окружения и `.env` (SSH/compose vars). На офлайн‑локали не заработает.
- В `Service` зашит базовый заголовок OAuth — не хранить реальные секреты в репозитории; ключи в `.env`.
- При пустых стилях/цветах убедиться, что SCSS файлы остаются валидными (пустые файлы допустимы).

## Verification
- После `npm run figma` появляются/обновляются `colors.scss`, `texts.scss`, `headers_*.scss`, `include.scss`.
- `npm run build && npm run postbuild` создаёт `dist/` и переносит ассеты в `templates/trimiata/bundle`.
- PoC: `figma:node -- --nodes=<id> --mode=demo` кладёт демо‑страницу и partials; SCSS линтится и собирается.

## Follow-ups
- Обновить `package.json` для явных npm‑скриптов figma:* (если нужно): `figma:node`, `figma:pull`, `figma:apply`.
- Поддержка экспорта ассетов (SVG/WEBP) в PoC и интеграция partials в компоненты.
- Убрать чувствительные значения OAuth в коде (перевести полностью на `.env`).



