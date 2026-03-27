# 2025-09-15 — Figma → Codegen (PoC) по node_id

Контекст
- В проекте уже реализован импорт токенов/стилей из Figma через `app/local/cron/figma.php` (шрифты, цвета, миксины).
- Требовалось добавить тестовую автоматизацию создания верстки (HTML+SCSS) напрямую из макетов Figma по `node_id` с соблюдением правил проекта (BEM, data-role, layout → flex, без логики в шаблонах).

Что сделано (PoC)
- Pull (REST) → JSON DSL:
  - `system/figma/pull/index.js` — читает узлы по `node_id` (DevMode API), строит упрощенный DSL: layout (flex direction/gap/padding), text styles (font-size/line-height/weight/letter-spacing), fill/border.
- Codegen → HTML+SCSS:
  - `system/figma/codegen/index.js` — генерирует блок `block-<kebab>` и элементы `__*`, добавляет `data-role`, пишет SCSS partials и агрегатор. Режимы: demo (HTML+CSS) и apply (только CSS).
- Одноступенчатый запуск:
  - `system/figma/run/index.js` — принимает `--nodes=<id>[,<id>]` и `--mode=demo|apply` и выполняет pull→codegen.
- NPM-скрипты:
  - `figma:node`, `figma:pull`, `figma:pull+build`, `figma:apply` (см. package.json в `app/local/changes/template`).

Как запускать
- Требуются ENV: `FIGMA_PROJECT_FILE_KEY`, `FIGMA_PERSONAL_ACCESS_TOKEN` (в окружении/CI).
- Демонстрация (HTML+CSS):
  - `cd app/local/changes/template && npm run figma:node -- --nodes=15099-208772 --mode=demo`
  - Результат: `dist/figma-demo/index.html`, `demo.css` и исходники `src/figma-demo/blocks/*`.
- Применение (CSS без демо):
  - `npm run figma:node -- --nodes=15099-208772 --mode=apply`
  - Результат: `dist/figma-apply/apply.css`, исходники `src/styles/figma/blocks/*`.

Решения/правила
- BEM: корневой класс `block-<kebab>`, элементы `__*`, модификаторы зарезервированы.
- `data-role`: назначается из имени ноды (очищенный kebab-case).
- Layout: auto-layout → flex-direction/gap/padding; alignment → align-items/justify-content.
- Строгая отделенность данных/логики: шаблоны без PHP-логики, только структура.

Ограничения (PoC)
- Требуются корректные ENV и доступ к Figma API; нет ещё плагина-экспортёра (источник DSL — REST DevMode).
- Семантика компонентов/вариантов (variants → модификаторы) — частично, планируется расширение.
- Экспорт ассетов (SVG/WEBP) — вне рамок PoC, будет добавлен.

Проверки
- Убедиться, что генерируется валидный HTML (BEM) и SCSS без линтер-ошибок; demo.html визуально соответствует макету по базовой геометрии/стилям.

Дальнейшие шаги
- Ввести поддержку variants→модификаторов, экспорт ассетов и интеграцию с Bitrix-компонентами через инклуды.
- Опционально: Figma Plugin для точной сериализации DSL (устойчивее к структуре нод).

Статус
- Тестовая реализация (PoC) для ускорения верстки и уменьшения рутины, не для продакшн полного покрытия страниц.


