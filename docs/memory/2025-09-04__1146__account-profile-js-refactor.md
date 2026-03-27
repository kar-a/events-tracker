# Task Memory Card

Date: 2025-09-04
Task Key: 1146
Title: Рефакторинг ЛК «Редактирование профиля»: новый компонент + JS правила

## Context
- Старый JS (editPersonalData.js) мигрирован на модуль AppProfileEdit с data-role и инициализацией из App.js.
- Шаблон содержал логику/ORM; сохранение профиля через full submit работало нестабильно.
- Требовались единые правила для компонентов/шаблонов/JS и кроссплатформенная сборка.

## Key Decisions
- Компонент app:account.edit: логика в class.php (D7 getRow(select)), шаблон в trimiata/main, .default пустой → чище разделение ответственности.
- API /api/user/save-profile.php: Ctx::request(), diff-only, обновление через \CUser->Update, UF коды из App\User\Helper → надёжное сохранение.
- JS AppProfileEdit: отправка FormData напрямую в AppApi, toast/loader, сбор даты рождения, нормализация поведения кнопки.
- Булевые UF нормализуются в '1'/'0' в prepareData() → корректный рендер чекбоксов.
- After-build перенесён из shell в Node (webpack.afterbuild.js), postbuild в package.json → кроссплатформенность.

## Code Touchpoints
- app/local/components/app/account.edit/{class.php,.description.php,.parameters.php}
- app/local/templates/trimiata/components/app/account.edit/main/template.php
- app/api/user/save-profile.php
- app/local/templates/trimiata/js/{AppApi.js,AppProfileEdit.js}
- docs/{modules-and-components.md,frontend-js-style.md,CHANGELOG.md}
- app/local/changes/template/{package.json,webpack.afterbuild.js}

## Gotchas (Pitfalls)
- FormData нельзя переупаковывать: отправлять исходный экземпляр в XHR.
- Не держать логику/ORM в шаблонах: только вывод и data-role.
- Для пользователя обновления — только через \CUser->Update (проектное исключение), не D7 update.
- Булевые UF могут возвращаться как null/0: нормализовать для шаблона.

## Verification
- Изменить улицу → Сохранить → Перезагрузка: значение сохранено.
- Переключить чекбоксы подписок → Сохранить → Перезагрузка: отражаются верно.
- Загрузка/удаление аватара: превью и флаг del корректны, после сабмита данные обновляются.

## Follow-ups
- Добавить unit/integ тесты API профиля (только под dev окружение).
- Вынести общие UI‑паттерны форм (валидация/submit) в AppForm для ЛК.
