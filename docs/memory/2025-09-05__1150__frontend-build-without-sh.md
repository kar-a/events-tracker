## 1150: Сборка фронта без sh (Node afterbuild)

Context
- Ранее postbuild выполнялся shell‑скриптом (WSL), что ломало сборку на Windows и вызывало различия локально/в CI.
- Задача: убрать зависимость от bash/WSL и сделать кроссплатформенную сборку.

Key Decisions
- В `package.json` добавлен скрипт `postbuild: node webpack.afterbuild.js` (без sh).
- Создан `webpack.afterbuild.js` на Node: удаление `templates/trimiata/bundle`, копирование `dist` → `bundle` и `src/img` → `bundle/img`.
- Скрипт завершает процесс с кодом 1, если не найден `dist` — гарантирует предсказуемые сбои в CI.

Code Touchpoints
- `app/local/changes/template/package.json` — скрипты `build` и `postbuild`.
- `app/local/changes/template/webpack.afterbuild.js` — кроссплатформенные операции файловой системы (fs/promises).

Gotchas
- Путь назначения — всегда `app/local/templates/trimiata/bundle`; не дублировать другие каталоги назначения.
- Warnings webpack/autoprefixer допустимы; ошибки — блокируют пайплайн.

Verification
- Локально: `npm run build && npm run postbuild` — бандл появляется в `app/local/templates/trimiata/bundle`.
- Проверить, что скопированы и ассеты из `dist`, и изображения из `src/img` (если каталог есть).

Follow-ups
- Перенести figma‑pipeline на Node (сейчас `npm run figma` использует WSL bash) — отдельная задача.

