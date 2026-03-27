## 1167: Robots.txt — разрешить CSS/JS/изображения/шрифты при общем Disallow

Context
- Исторически `app/.robots.txt` блокировал служебные разделы (`/bitrix/`, `/local/`, `/upload/` и др.), что могло мешать поисковикам корректно рендерить страницы (оценка мобильной пригодности, CLS и т.д.).

Key Decisions
- Сохраняем Disallow для служебных зон, но явно разрешаем ассеты:
  - Allow: `/bitrix/*.js`, `/bitrix/*.css`
  - Allow: `/local/templates/trimiata/*.(css|js|svg|png|jpg|jpeg|webp|woff|woff2)`
  - Allow: `/upload/resize_cache/`
- Сохраняем `Crawl-delay: 1`, Clean-param для UTM/служебных GET.
- Жёсткие Disallow для агрессивных краулеров (Ahrefs, Semrush*, MJ12 и др.).

Code Touchpoints
- `app/.robots.txt` — актуальный список Allow/Disallow/Clean-param.
- Документация: `docs/security-and-quality.md` (раздел Robots), `docs/knowledge-map.md` (SEO/Robots).

Gotchas
- Не добавлять Allow шире необходимого (например, весь `/upload/`), чтобы не расширить индекс лишними файлами.
- Следить за консистентностью `robots.php` (Host/Sitemap) и `app/.robots.txt`.

Verification
- Google/Yandex: инспекция URL — страница должна рендериться с подключёнными CSS/JS (нет ошибок blocked by robots).
- Логи/веб‑мастера: снижение ошибок рендера; стабильные отчёты мобильной пригодности.

