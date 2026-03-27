# Task Memory Card

Date: 2025-11-23
Task Key: 1216
Title: AJAX загрузка главного меню

## Context
- Проблема: Главное меню загружалось статически в DOM при каждой загрузке страницы, что ухудшало производительность и увеличивало время первоначальной загрузки страницы.
- Baseline: Компонент `app:catalog.section.list` с шаблоном `header` статически включался в `header.php` в контейнере `.menu_desktop`.

## Key Decisions
- AJAX загрузка меню через API endpoint `app/api/modal/menu.php` → Улучшение производительности за счёт ленивой загрузки → Меню загружается только при необходимости, уменьшается время первоначальной загрузки страницы
- Кеширование меню через serviceWorker (`cacheType: 'serviceWorker'`) → Оптимизация использования ресурсов → Меню загружается из кеша при последующих загрузках страницы
- Использование `data-menu-loaded` атрибута для проверки загрузки → Корректная работа с HTML комментариями → Избежание ложных срабатываний при проверке `innerHTML.trim()`
- Инициализация JS логики через `script` в ответе API → Корректная инициализация после AJAX загрузки → `AppHeaderMenu` и `AppSectionsMenu` работают после динамической загрузки контента
- Удаление проверок `if (this.isInitialized()) return;` в конструкторах `AppHeaderMenu` и `AppSectionsMenu` → Возможность переинициализации после AJAX загрузки → Меню корректно работает после динамической загрузки

## Code Touchpoints
- `app/api/modal/menu.php` → API endpoint для загрузки меню через компонент `app:catalog.section.list` с шаблоном `header`, возврат `html` и `script` в ответе API
- `app/local/templates/trimiata/header.php` → Контейнер `.menu_desktop` заменён на контейнер с `data-role="header-menu-container"` и `data-menu-loaded="false"`, статическое включение компонента удалено
- `app/local/templates/trimiata/js/App.js` → Метод `loadHeaderMenu()`: AJAX загрузка меню с кешированием через serviceWorker, обработка `html`, `script` и `storage` из ответа API, установка `data-menu-loaded="true"` после загрузки, вызов `App.initHeaderMenu()` и `App.initSectionsMenu()` через `script` в ответе API
- `app/local/templates/trimiata/js/AppHeaderMenu.js` → Удалена проверка `if (!this.app || !this.app.isInitialized())` из конструктора для корректной инициализации после AJAX загрузки
- `app/local/templates/trimiata/js/AppSectionsMenu.js` → Удалена проверка `if (!this.app || !this.app.isInitialized())` из конструктора для корректной инициализации после AJAX загрузки

## Gotchas (Pitfalls)
- Проверка загрузки меню через `innerHTML.trim() !== ''` не работает, если в контейнере есть HTML комментарии. Использование `data-menu-loaded` атрибута решает эту проблему.
- Инициализация JS логики меню должна происходить после вставки HTML в DOM. Использование `script` в ответе API с задержкой 200ms обеспечивает правильную последовательность.
- Кеширование через serviceWorker требует правильной настройки `cacheType: 'serviceWorker'` в методе `apiRequest()`.

## Verification
- Проверить, что главное меню загружается через AJAX при первой загрузке страницы
- Проверить, что меню кешируется в serviceWorker и загружается из кеша при последующих загрузках
- Проверить, что JavaScript логика меню (`AppHeaderMenu`, `AppSectionsMenu`) корректно работает после AJAX загрузки
- Проверить, что меню не загружается повторно, если уже загружено (проверка `data-menu-loaded`)
- Проверить работу меню на разных страницах сайта

## Follow-ups
- Возможна оптимизация: предзагрузка меню в фоне для улучшения UX
- Возможна оптимизация: использование Intersection Observer для загрузки меню только при приближении к header

