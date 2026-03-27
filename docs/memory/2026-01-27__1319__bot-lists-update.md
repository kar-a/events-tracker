# Задача 1319: Усиление безопасности - Расширение списка поисковых ботов

**Дата:** 2026-01-27  
**Задача:** 1319  
**Тип:** Безопасность, Оптимизация производительности

---

## Контекст

Обновлены списки ботов в файле `app/local/php_interface/config/uri.php` для улучшения безопасности и производительности сайта. Списки используются методами `Router::getInstance()->isBot()` и `Router::getInstance()->isGoodBot()`.

---

## Ключевые решения

### Разделение на goodBots и bots

**goodBots** - легитимные боты с полным доступом:
- Поисковые системы (Google, Bing, Yandex, Baidu, DuckDuckGo, Seznam)
- Социальные сети (Facebook, LinkedIn, Twitter/X, VK, WhatsApp, Telegram и др.)
- AI краулеры для индексации (OAI-SearchBot, PerplexityBot) - **НЕ для обучения**
- SEO инструменты (AhrefsBot, SemrushBot, Majestic)
- Другие легитимные сервисы (2GIS, Applebot, Wayback Machine)

**bots** - все известные боты (включая goodBots):
- Все из goodBots
- AI краулеры для обучения моделей (GPTBot, ClaudeBot, ChatGPT-User, Google-Extended)
- Другие известные боты

### Принцип работы

Методы используют `stripos()` для поиска подстроки в User-Agent:
```php
foreach ($this->bots as $bot) {
    if (stripos($userAgent, $bot) !== false) {
        return $bot;
    }
}
```

Все значения приводятся к нижнему регистру при загрузке конфигурации.

---

## Использование в коде

### isGoodBot()

**Использование:**
- `BeforeProlog::checkScanners()` - пропуск хороших ботов при проверке сканеров уязвимостей

**Логика:** Хорошие боты не должны блокироваться как сканеры уязвимостей.

### isBot()

**Использование:**
1. `BeforeProlog::checkScanners()` - пропуск всех ботов при проверке `noReferer`
2. `BeforeProlog::run()` - пропуск редиректов для ботов
3. `ApiResult::__construct()` - пропуск CSRF проверки для ботов
4. `Seo\Content\Helper::generateTexts()` - пропуск генерации SEO контента для ботов
5. `system.auth.full` - пропуск AJAX действий для ботов
6. `search.page` - пропуск отчетов поиска для ботов

**Логика:** Боты не должны проходить через обычные проверки безопасности и не должны генерировать динамический контент.

---

## Обновления списков

### Добавленные поисковые системы

- **Baiduspider** (китайская поисковая система)
- **DuckDuckBot** (приватная поисковая система)
- **SeznamBot** (чешская поисковая система)
- Специализированные версии Googlebot (Image, Video, News, Mobile)
- Специализированные версии Yandexbot (Images, Video, Media, Blogs, News)

### Добавленные социальные сети

- **Meta/Facebook:** facebookexternalhit, facebookcatalog, facebot, facebookbot, meta-webindexer
- **LinkedIn:** linkedinbot, linkedin
- **Twitter/X:** twitterbot, twitter, x-bot
- **Другие:** Instagram, Pinterest, Discord, Skype, Viber, Line, Snapchat, Tumblr, Reddit, Slack, Chime

### Добавленные AI краулеры

**Для индексации (goodBots):**
- OAI-SearchBot (ChatGPT поиск, НЕ обучение)
- PerplexityBot (индексация, НЕ обучение)

**Для обучения (только в bots):**
- GPTBot (OpenAI обучение)
- ChatGPT-User (OpenAI браузинг)
- ClaudeBot (Anthropic обучение)
- Claude-Web (Anthropic браузинг)
- Google-Extended (Gemini/Bard обучение)
- Perplexity-User (Perplexity браузинг)
- CCBot (Common Crawl)
- Amazonbot, PetalBot

### Добавленные SEO инструменты

- AhrefsBot, AhrefsSiteAudit
- SemrushBot
- Majestic, MJ12bot
- Moz.com, Rogerbot
- Screaming Frog SEO Spider
- Blexbot, Dotbot
- SPbot, Spider
- Другие легитимные SEO краулеры

### Добавленные архиваторы

- Wayback Machine (archive.org_bot, wayback, web.archive.org)
- IA_Archiver

---

## Источники информации

1. **Human Security** - Comprehensive 2026 List of Web Crawlers and Good Bots
2. **DataDome** - Updated Crawler List based on top 2025 crawlers
3. **Arcjet** - Well-Known Bots GitHub repository
4. **Официальная документация:**
   - Google Crawlers Overview
   - Meta Web Crawlers
   - OpenAI GPTBot Documentation
   - Perplexity Crawlers Documentation
5. **Search Engine Journal** - Complete Crawler List For AI User-Agents

---

## Важные замечания

### AI краулеры для обучения

AI краулеры, которые собирают данные для обучения моделей (GPTBot, ClaudeBot, Google-Extended), **НЕ включены** в `goodBots`, так как они:
- Потребляют много ресурсов
- Не приносят прямой пользы сайту (не индексируют для поиска)
- Могут быть заблокированы через robots.txt

Они включены только в общий список `bots`, чтобы их можно было идентифицировать и при необходимости обработать отдельно.

### Безопасность

- Все боты проверяются через `stripos()`, что позволяет находить подстроки в User-Agent
- Злоумышленники могут подделать User-Agent, поэтому проверка ботов не является единственной мерой безопасности
- Хорошие боты пропускаются при проверке сканеров уязвимостей, но другие проверки безопасности остаются активными

### Производительность

- Боты пропускают генерацию динамического SEO контента
- Боты пропускают AJAX действия
- Боты пропускают отчеты поиска
- Это снижает нагрузку на сервер при большом количестве запросов от ботов

---

## Файлы изменений

- `app/local/php_interface/config/uri.php` - обновлены списки `bots` и `goodBots`

---

## Проверка работы

1. Проверить, что хорошие боты не блокируются как сканеры уязвимостей
2. Проверить, что боты пропускают CSRF проверку в API
3. Проверить, что боты не генерируют динамический SEO контент
4. Мониторить логи на наличие ложных срабатываний

---

## Следующие шаги

1. Мониторить логи блокировок на наличие легитимных ботов
2. Регулярно обновлять списки при появлении новых ботов
3. Рассмотреть возможность добавления проверки IP-адресов для критичных ботов (Googlebot, Bingbot)
4. Добавить документацию в `docs/security.md` о работе с ботами

---

## Связанные задачи

- Задача 1318: Расширение списка адресов детекта сканнеров уязвимостей
- Документ безопасности: `docs/security.md`
