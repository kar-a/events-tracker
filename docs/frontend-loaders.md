# Система лоадеров и динамический контент

## Обзор

В проекте используется единая система лоадеров для индикации загрузки в различных сценариях:
- **Глобальный лоадер** (`showLoader`) - для страниц и модалей
- **Лоадер кнопок** (`showBtnLoading`) - для кнопок и форм
- **Лоадер блоков** (`showBlockLoading`) - для контейнеров контента

Дополнительно этот документ описывает паттерны работы с динамически загружаемым контентом (offcanvas/aside/CupertinoPane), основанные на реализации быстрой покупки (task 1175): вместо открытия новых модалей используется замена содержимого существующих контейнеров с событийным управлением лоадерами.

## API методов

### `app.showLoader(closeEvent)`
**Назначение**: Глобальный лоадер для страниц и модалей
**Параметры**:
- `closeEvent` (string, optional) - событие для автоматического скрытия лоадера

**Использование**:
```javascript
// Лоадер без автоматического скрытия
app.showLoader();

// Лоадер с автоматическим скрытием по событию
app.showLoader('_loaded');
```

**Принцип работы**:
1. Создает элемент `.body_spinner` с анимацией
2. Если передан `closeEvent`, слушает это событие для автоматического скрытия
3. При получении события плавно скрывает и удаляет лоадер

### `app.showBtnLoading(button)`
**Назначение**: Лоадер для кнопок и интерактивных элементов
**Параметры**:
- `button` (HTMLElement) - элемент кнопки

**Принцип работы**:
1. Фиксирует размеры кнопки
2. Блокирует кнопку (`disabled = true`)
3. Добавляет класс `loading`
4. Слушает событие `_loaded` на кнопке для автоматического скрытия

**Использование**:
```javascript
app.showBtnLoading(button);
// После завершения операции
button.dispatchEvent(new Event('_loaded'));
```

### `app.showBlockLoading(block, clearContent)`
**Назначение**: Лоадер для контейнеров контента
**Параметры**:
- `block` (HTMLElement) - контейнер
- `clearContent` (boolean, default: true) - очищать ли содержимое

**Принцип работы**:
1. Добавляет классы `shown` и `loading`
2. Опционально очищает содержимое
3. Слушает событие `_loaded` для автоматического скрытия

Примечание: метод `hideLoader` удалён. Для закрытия лоадеров используйте только событийную модель через `_loaded`.

## События

### `_loaded`
Универсальное событие для скрытия лоадеров. Используется во всех типах лоадеров:

```javascript
// Для кнопок
button.dispatchEvent(new Event('_loaded'));

// Для блоков
block.dispatchEvent(new Event('_loaded'));

// Для глобального лоадера
document.dispatchEvent(new Event('_loaded'));
```

Рекомендуется диспатчить `_loaded` только после полного завершения всех операций с DOM (вставка HTML, инициализация обработчиков, ребиндинги слайдеров/Fancybox), чтобы избежать «мигания» лоадеров.

## Паттерны использования

### 1. AJAX запросы
```javascript
app.showBtnLoading(button);
fetch('/api/endpoint')
  .then(response => response.json())
  .then(data => {
    // Обработка данных
    button.dispatchEvent(new Event('_loaded'));
  })
  .catch(error => {
    button.dispatchEvent(new Event('_loaded'));
  });
```

### 2. Загрузка модалей
```javascript
app.showLoader('_loaded');
app.loadAjaxModal('modal-path', params, true)
  .then(() => {
    document.dispatchEvent(new Event('_loaded'));
  });
```

### 3. Загрузка контента в блоки
```javascript
app.showBlockLoading(container);
fetch('/api/content')
  .then(response => response.text())
  .then(html => {
    container.innerHTML = html;
    container.dispatchEvent(new Event('_loaded'));
  });
```

### 4. Динамическая замена контента в модалях (Quick Buy и др.)

Проблема: создание множества offcanvas/CupertinoPane приводит к накоплению backdrop'ов и конфликтам при закрытии. Решение: загружать HTML и заменять `innerHTML` существующего контейнера, затем переинициализировать динамические элементы.

```javascript
// Пример: переключение свойств товара в быстрой покупке
app.delegate(document, 'click', '[data-role=quick-buy-property]', (e) => {
  e.preventDefault();

  const link = e.target.closest('a');
  const elementId = parseInt(link.getAttribute('data-item'));
  if (!elementId) return;

  app.showLoader('_loaded');
  const params = encodeURIComponent(app.serialize({ elementId }));
  app.api.loadAjaxModal('product-quick-buy', 'json', params).then((result) => {
    if (!result?.success || !result?.template) return;

    // Desktop: offcanvas
    const currentOffcanvas = document.querySelector('.offcanvas-quick_buy.show');
    if (currentOffcanvas) {
      const parseDiv = document.createElement('div');
      parseDiv.innerHTML = result.template;
      const newContent = parseDiv.querySelector('.offcanvas-content');
      const currentContent = currentOffcanvas.querySelector('.offcanvas-content');
      if (currentContent && newContent) {
        currentContent.innerHTML = newContent.innerHTML;
        $.initialize(currentContent);
        component.replaceButtons();
      }
    }

    // Mobile: CupertinoPane контейнер
    let currentPane = document.querySelector('.mobile-pane__container__quick_buy');
    if (!currentPane) {
      const allPanes = document.querySelectorAll('.mobile-pane__container');
      for (const pane of allPanes) {
        if (pane.querySelector('[data-role=modal-content]')) { currentPane = pane; break; }
      }
    }
    if (currentPane) {
      const parseDiv = document.createElement('div');
      parseDiv.innerHTML = result.template;
      const newPane = parseDiv.querySelector('[data-role=modal-content]');
      if (newPane) {
        currentPane.innerHTML = newPane.innerHTML;
        $.initialize(currentPane);
        setTimeout(() => {
          component.initEvents();
          component.initAddButtons();
          component.initDeleteButtons();
          component.initCoupons();
        }, 100);
        component.replaceButtons();
      }
    }

    // Завершаем лоадер после полной вставки/переинициализации
    document.dispatchEvent(new Event('_loaded'));
  }).catch(() => {
    component.replaceButtons();
    document.dispatchEvent(new Event('_loaded'));
  });
});
```

После замены контента обязательно выполните:
- `$.initialize(container)` — для ребиндинга слайдеров/Fancybox/инициализаций по `data-role`;
- `component.replaceButtons()` — для актуализации кнопок (корзина/избранное/сравнение и т.д.).

## Стили

### `.body_spinner`
Глобальный лоадер с анимацией:
```css
.body_spinner {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 255, 0.9);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s;
}

.body_spinner.active {
  opacity: 1;
}
```

### `.loading`
Класс для кнопок и блоков:
```css
.loading {
  position: relative;
  pointer-events: none;
}

.loading::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 20px;
  height: 20px;
  margin: -10px 0 0 -10px;
  border: 2px solid #ccc;
  border-top: 2px solid #007bff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}
```

## Интеграция с компонентами

### AppBasket
- `showBtnLoading` для кнопок добавления в корзину
- `showLoader('_loaded')` для быстрой покупки
- `showBlockLoading` для удаления товаров
- Динамическая замена контента в быстрой покупке через `app.api.loadAjaxModal()` с последующей переинициализацией (`$.initialize`, `component.replaceButtons()`).

### AppSmartFilter
- `showLoader` для применения фильтров
- `showBtnLoading` для кнопок сортировки

### AppForm
- `showBtnLoading` для кнопок отправки форм

### CupertinoPane (мобильные панели)
### AppApi / App.loadAjax*

Контракты вызовов, используемых для модальных/динамических сценариев:

```javascript
// AppApi.loadAjaxModal(modalName, type='json', params, json=true)
// - modalName: строка, например 'product-quick-buy'
// - type: 'json' | 'text'; при 'json' обращается к /api/modal/<modalName>/ (POST)
// - params: строка (urlencoded serialize) или объект (см. product-quick-buy)
// Возвращает JSON: { success, template? , html? , script? , storage? }

this.api.loadAjaxModal('product-quick-buy', 'json', params).then((result) => {
  if (result.success && result.template) { /* используем HTML */ }
});

// App.loadAjaxAside(path, type, params, showBackdrop?) — обёртка, открывающая offcanvas
// - path: строка; при type==='api' — относительный путь в /api/, иначе сайт/текст
// - type: 'api' | 'sitePage'
// - params: строка (urlencoded serialize) или объект
// Возвращает Promise, по которому после показа сайдбара можно выполнить инициализации.

app.loadAjaxAside('product-quick-buy', 'api', params, true).then(() => {
  document.dispatchEvent(new Event('_loaded'));
});
```

Примечания:
- Для смены свойств в быстрой покупке используем AppApi.loadAjaxModal и замену содержимого текущего контейнера, не App.loadAjaxAside.
- Для безопасности X-Bitrix-Csrf-Token проставляется автоматически (см. `AppApi.request`).
- Контейнер быстрой покупки размечаем как `.mobile-pane__container__quick_buy`; к родительскому `.pane` добавляется класс `pane_fast_buy` только для quick buy.
- При обработке кликов внутри мобильной панели используйте `e.stopPropagation()` в кастомных обработчиках (например, `changeOffer`) для предотвращения конфликтов с глобальными делегатами.
- После замены контента в панели переинициализируйте обработчики c небольшим таймаутом (`setTimeout(..., 100)`), чтобы дождаться применения стилей/трансформаций CupertinoPane.

## Лучшие практики

1. **Всегда используйте события** - не вызывайте методы скрытия вручную
2. **Обрабатывайте ошибки** - скрывайте лоадеры в `catch` блоках
3. **Используйте правильный тип лоадера**:
   - `showLoader` - для страниц и модалей
   - `showBtnLoading` - для кнопок
   - `showBlockLoading` - для контейнеров
4. **Не блокируйте UI** - лоадеры должны быть неблокирующими
5. **Тестируйте на мобильных** - убедитесь, что лоадеры работают на всех устройствах
6. **При замене контента** - используйте `app.showLoader('_loaded')` и `document.dispatchEvent(new Event('_loaded'))` для автоматического скрытия
7. **Для динамических модалей** - лоадер должен скрываться после завершения всех операций с DOM

8. **Fancybox/галерея** - после замены DOM повторно привяжите обработчики:
```javascript
if (window.Fancybox) {
	window.Fancybox.bind('[data-fancybox="gallery"]', {});
}
```

9. **Не создавайте новые offcanvas/CupertinoPane при смене свойств** — используйте только замену содержимого существующих контейнеров.

## Отладка

### Проверка активных лоадеров
```javascript
// Глобальный лоадер
console.log(document.querySelector('.body_spinner'));

// Лоадеры кнопок
console.log(document.querySelectorAll('.loading'));

// Лоадеры блоков
console.log(document.querySelectorAll('.loading.shown'));
```

### Принудительное скрытие
```javascript
// Скрыть все лоадеры
document.dispatchEvent(new Event('_loaded'));
```

### Проверка конфликтов обработчиков
```javascript
// Отключить всплытие для конкретной кнопки в мобильной панели
btn.addEventListener('click', (e) => { e.stopPropagation(); /* ... */ }, { capture: true });
```

### Быстрый чек-лист при проблемах
1) Диспатчится ли `_loaded` после вставки HTML и переинициализаций?
2) Выполнен ли `$.initialize(container)` для нового DOM?
3) В мобильной панели: есть ли контейнер `.mobile-pane__container__quick_buy` и не конфликтуют ли обработчики (есть `stopPropagation()`)?
4) Fancybox/слайдеры перебиндены после замены DOM?
