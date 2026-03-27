# Task Memory Card

Date: 2025-08-21
Task Key: git-github
Title: GitHub подключение (SSH), проверки и возможности ассистента

## Context
- Нужно зафиксировать метод подключения к GitHub (SSH) и базовые операции.
- Подтвердить и задокументировать прямой read-only доступ ассистента к GitHub.
- Затронутые файлы: `docs/git-and-github.md`, `docs/README.md`, `docs/runbook.md`, `docs/CHANGELOG.md`, `.github/commit-summary.txt`.

## Key Decisions
- Официальный remote-алиас — `trimiata` → `git@github.com:trimiata/bitrix-dev.git` → Стандартизация URL.
- Проверки доступа — `ssh -T`, `git ls-remote`, `git fetch --dry-run` → Безопасные (read-only) проверки.
- ИИ‑ассистент может выполнять fetch/ls-remote/просмотр PR → Прозрачность возможностей для команды.

## Code Touchpoints
- `docs/git-and-github.md` → Новая страница с SSH, проверками, командами.
- `docs/README.md` → Ссылка на гайд.
- `docs/runbook.md` → Раздел Git/GitHub с ссылкой.
- `docs/CHANGELOG.md` → Запись в Unreleased/Added.
- `.github/commit-summary.txt` → Обновлён Effects/Intent для CI уведомлений.

## Gotchas (Pitfalls)
- Не использовать HTTPS‑URL для push (во избежание паролей/токенов в консоли).
- Не добавлять секреты/токены в репозиторий или changelog.
- Следить за отсутствием интерактива в скриптах (добавлять `--no-pager`, `--dry-run`).

## Verification
- `git remote -v` показывает `trimiata` на SSH URL.
- `ssh -T git@github.com` проходит.
- `git ls-remote --heads --tags trimiata` и `git fetch --dry-run trimiata` завершаются без ошибок.
- Линты `docs/git-and-github.md` без предупреждений.

## Follow-ups
- При необходимости добавить раздел про PR‑флоу и шаблоны PR.
- Включить краткий раздел в onboarding чек‑лист.


