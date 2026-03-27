# Project context and goals

## Business context
Trimiata is a jewelry online store. The goal is to build a recommendation system that works well even when users are not logged in.

## Why this is hard
Most users are anonymous. That means the recommendation system cannot rely on account history only. It must work with:
- anonymous browser identity
- session behavior
- product views
- cart additions
- purchases
- channel/device context
- imported behavioral history from Yandex Metrika

## Product objective
Launch a block:
- **“Вам может понравиться”**

Where:
- product page
- category/listing page
- homepage
- later cart and other surfaces

## MVP success criteria
- higher CTR on recommendation block
- more add-to-cart after recommendation click
- measurable purchase uplift
- stable low-latency serving

## Strategic objective
Create a long-term recommendation platform that can combine:
- website events
- Yandex Metrika logs
- future mobile app events
- future CRM / push / email signals
- product attributes
- image/text similarity later

## Existing technical context from chat
- PHP + Bitrix project
- Yandex integration classes exist (`Service.php`, `Webmaster.php`, `SmartCaptcha.php`)
- A `getService(...)` helper pattern is already used in Yandex service classes
- A first event contract file has already been prepared: `code/TrimiataEventSchema.php`

## Design principle
Start from an engineering-strong MVP:
1. unified event model
2. event pipeline
3. historical import
4. item-to-item recommendations
5. A/B and measurement
Then expand to more advanced personalization and ML.
