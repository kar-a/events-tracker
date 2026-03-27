# Git и GitHub: подключение и использование

Цель: зафиксировать единый способ подключения к GitHub и базовые операции, а также отметить, что ИИ‑ассистент имеет прямой доступ к репозиторию через GitHub (SSH) и может выполнять безопасные операции чтения (fetch, просмотр PR/коммитов).

## Подключение по SSH

- Рекомендуемый протокол: SSH (без паролей; ключ в агенте).
- URL репозитория: `git@github.com:trimiata/bitrix-dev.git`.
- Базовая настройка remote (используем алиас `trimiata`):

```bash
git remote add trimiata git@github.com:trimiata/bitrix-dev.git
git remote -v
```

## Проверка доступа (не изменяет состояние)

```bash
# Тест SSH‑аутентификации к GitHub
ssh -T git@github.com

# Список ссылок на удалённые ветки/теги
git ls-remote --heads --tags trimiata

# Пробный fetch без загрузки/изменений
git fetch --dry-run trimiata
```

## Типовые операции

- Получить обновления: `git fetch trimiata` → `git checkout <branch>` → `git pull --ff-only`.
- Отправить изменения: `git push trimiata <branch>`.
- Создать ветку под задачу: `git checkout -b <branch>` (см. политику веток в README → Gitflow).
- Открыть PR: через интерфейс GitHub, выбирая `base: main` (или целевую по Gitflow) и `compare: <branch>`.

## Непрерывная работа без интерактивных зависаний

- Для длинных выводов используйте ключ без пейджера: `git --no-pager <command>`.
- В CI/скриптах избегайте интерактива: добавляйте флаг/опции без подтверждения.

## Политика безопасности

- Секреты/токены не коммитим. Используем `.env`/vault и переменные окружения.
- Не публикуем приватные ссылки, дампы БД и содержимое `app/.env`.

## Возможности ИИ‑ассистента (подтверждено)

- Ассистент подключён к GitHub и может:
  - читать удалённые ветки/теги: `git ls-remote`, `git fetch --dry-run trimiata`;
  - просматривать PR/коммиты и диффы для ревью;
  - использовать `git remote show trimiata` для инспекции удалённого.
- Текущее состояние в локальном репозитории:
  - настроен remote `trimiata → git@github.com:trimiata/bitrix-dev.git` (fetch/push);
  - тесты доступа выполнены успешно (ошибок при `ls-remote`/`fetch --dry-run` нет).

## Быстрый чек‑лист

- [ ] Настроен SSH‑ключ и добавлен в GitHub (Settings → SSH and GPG keys).
- [ ] `git remote -v` показывает `trimiata` на SSH‑URL.
- [ ] `ssh -T git@github.com` проходит без ошибок.
- [ ] `git ls-remote`/`git fetch --dry-run trimiata` выполняются успешно.
