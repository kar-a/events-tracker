# Система событий и хуков (Bitrix D7)

## Обзор

В проекте используется система событий Bitrix D7 для обработки различных жизненных циклов приложения. События регистрируются в `app/local/php_interface/lib/events/inc.php`.

## Основные группы событий

### 1. События main (основные)
- **OnBeforeProlog** - нормализация URL, редиректы, SEO, UTM
- **OnProlog** - инициализация контекста
- **OnEpilog** - финальная обработка страницы

### 2. События sale (заказы)
- **OnSaleOrderSaved** - сохранение заказа
- **OnSaleOrderStatusChange** - смена статуса
- **OnSaleDeliveryServiceCalculate** - расчет доставки
- **OnSalePaymentSystemCalculate** - расчет оплаты

### 3. События iblock (каталог)
- **OnAfterIBlockElementAdd** - добавление элемента
- **OnAfterIBlockElementUpdate** - обновление элемента
- **OnAfterIBlockElementDelete** - удаление элемента

## Регистрация событий

```php
// В app/local/php_interface/lib/events/inc.php
use Bitrix\Main\EventManager;

$eventManager = EventManager::getInstance();

// Регистрация обработчика через array callable
$eventManager->addEventHandler(
    'main',
    'OnBeforeProlog',
    ['\\App\\EventHandler\\Main\\BeforeProlog', 'init']
);
```

## Создание собственных событий

### 1. Создание класса-обработчика

```php
// app/local/php_interface/lib/events/main/BeforeProlog.php
namespace App\EventHandler\Main;

class BeforeProlog
{
	/**
	 * Нормализация URL/инициализация страницы
	 */
	public static function init(): bool
	{
		// ... логика
		return true;
	}
}
```

### 2. Подключение в inc.php

```php
// app/local/php_interface/lib/events/inc.php
require('main/BeforeProlog.php');
```

## Порядок выполнения событий

1. **OnBeforeProlog** - нормализация URL, редиректы
2. **OnProlog** - инициализация контекста, SEO
3. **Компоненты** - выполнение компонентов
4. **OnEpilog** - финальная обработка

## Лучшие практики

### 1. Идемпотентность
- Обработчики должны быть идемпотентными
- Проверяйте условия перед выполнением действий

### 2. Производительность
- Минимизируйте количество запросов к БД
- Используйте кеширование для тяжелых операций

### 3. Обработка ошибок
- Всегда обрабатывайте исключения
- Логируйте ошибки для отладки

## Примеры использования

### Нормализация URL
```php
public static function init()
{
    $request = \Bitrix\Main\Context::getCurrent()->getRequest();
    $uri = $request->getRequestUri();
    
    // Нормализация
    $uri = strtolower($uri);
    $uri = rtrim($uri, '/') . '/';
    
    // Редирект при необходимости
    if ($uri !== $request->getRequestUri()) {
        LocalRedirect($uri, true, '301 Moved Permanently');
    }
}
```

### SEO-инициализация
```php
public static function initSeo(): void
{
    global $APPLICATION;
    
    // Установка мета-тегов (пример)
    $APPLICATION->SetPageProperty('description', 'Описание страницы');
}
```

## Отладка событий

### 1. Логирование
```php
// Используем функцию ulogging из проекта
ulogging($data, 'event_debug', true, false);
```

### 2. Проверка регистрации
```php
$eventManager = EventManager::getInstance();
$handlers = $eventManager->getEventHandlers('main', 'OnBeforeProlog');
```

### 3. Отладочная функция pre()
```php
// Для отладки используем функцию pre() из проекта
pre($data, false); // Вывод без остановки
pre($data, true);  // Вывод с остановкой выполнения
```

## Интеграция с компонентами

События могут взаимодействовать с компонентами через:
- Глобальные переменные
- Кеш
- Сессии
- Параметры компонентов

## Мониторинг и производительность

- Используйте профилирование для тяжелых операций
- Мониторьте время выполнения обработчиков
- Логируйте медленные операции
