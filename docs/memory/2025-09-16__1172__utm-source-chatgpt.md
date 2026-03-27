## 1172 — UTM: определение источника chatgpt и расширение правил

Context
- В `BeforeProlog::saveUTM()` проставляются utm‑метки при их отсутствии на основе `HTTP_REFERER`.
- Требовалось добавить распознавание трафика из ChatGPT и расширить точность детекции популярных источников (карты, соцсети/мессенджеры, поисковики) и кликов без UTM.

Key Decisions
- ChatGPT: `chat.openai.com`/`chatgpt.com` → `utm_source=chatgpt`.
- Карты: `yandex.* /maps` → `yandex_maps`, `google.* /maps` → `google_maps`.
- Соцсети/мессенджеры: instagram/facebook/vk/ok/telegram/youtube/whatsapp/viber → соответствующие `utm_source`.
- Поисковики: yandex/google/bing/duckduckgo/mailru.
- Без UTM в ссылке используем click‑id параметры: gclid/yclid/fbclid/msclkid/ttclid/twclid/dclid/mc_eid для установки `utm_source` и, где уместно, `utm_medium`.

Code Touchpoints
- `app/local/php_interface/lib/events/main/BeforeProlog.php` → метод `saveUTM()`.

Gotchas
- При наличии любых UTM во входящем запросе авто‑детекция не выполняется (сохраняем явные параметры).
- Для `facebook` встречаются `__fbclid`/`fbclid` — поддержаны оба.
- Приводим host к нижнему регистру, учитываем поддомены (`l.instagram.com`, `lm.facebook.com`).

Verification
- Без utm, с `referer=https://chat.openai.com/...` → `utm_source=chatgpt`.
- `referer=https://www.google.com/maps?...` → `utm_source=google_maps`.
- `?gclid=...` без UTM → `utm_source=google`, `utm_medium=cpc`.
- При явных `utm_*` параметры не переопределяются.

Follow-ups
- Добавить сохранение `utm_content/utm_term`, если известны из параметров (когда появится кейс).

