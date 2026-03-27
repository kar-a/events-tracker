# Task Memory Card

Date: 2025-08-21
Task Key: ai-memory
Title: Onboarding/context plan + AI task memory system

## Context
- Требовалось формализовать план быстрого онбординга/контекста и реализовать постоянную память задач для ИИ.
- Цели: ускорить принятие решений, обеспечить накопление опыта, упростить доступ к памяти.
- Ограничения: не трогаем рантайм‑код, соблюдаем SEO/URL политику, минимальные правки.

## Key Decisions
- Создать раздел памяти в репозитории: `docs/memory/{INDEX.md,TEMPLATE.md}` → просто, версионируемо, быстро доступно.
- Зафиксировать план онбординга/контекста в `docs/onboarding-and-context.md` → единая отправная точка.
- Расширить правила ИИ `.cursor/rules/bitrix.mdc` (context_plan, fast_context_methods, memory_process, memory_lookup) → стандартизация процесса.
- Добавить быстрые команды в пресет: `/context`, `/remember`, `/recall`, `/memindex` → удобный вызов.
- Сослаться на память в `docs/README.md`; добавить запись в `docs/CHANGELOG.md` → прозрачность.

## Code Touchpoints
- docs/memory/INDEX.md — индекс памяти (актуализирован ссылкой).
- docs/memory/TEMPLATE.md — шаблон карточки памяти.
- docs/onboarding-and-context.md — план онбординга/контекста.
- docs/README.md — добавлена ссылка на память задач.
- .cursor/rules/bitrix.mdc — добавлены context_plan/fast_context_methods/memory_plan/memory_process/memory_lookup.
- .cursor/presets/bitrix-fullstack-assistant.json — добавлены команды `/context`, `/remember`, `/recall`, `/memindex`.
- docs/CHANGELOG.md — добавлена запись в Unreleased.
- .github/commit-summary.txt — создан текст для CI.

## Gotchas (Pitfalls)
- Не хранить секреты/идентификаторы в карточках.
- Поддерживать актуальность INDEX.md; имя файла по паттерну `YYYY-MM-DD__task-key__slug.md`.
- Не смешивать память с детальными инструкциями по миграциям/секретам.

## Verification
- Открываются ссылки из README и INDEX.md.
- В пресете видны команды `/context`, `/remember`, `/recall`, `/memindex`.
- В правилах присутствуют разделы memory_process/memory_lookup.

## Follow-ups
- Заполнять карточки для нетривиальных задач (каталог/фильтр/ImShop/SEO) по мере выполнения.
- При накоплении объёма — добавить теги и оглавление по темам в INDEX.md.
