# Gaps, unknowns, and review notes

This is the review section: what is still unclear, risky, or deserves validation.

## 1. Exact current Yandex integration code
The chat referenced existing files:
- `Service.php`
- `Webmaster.php`
- `SmartCaptcha.php`

The exact full file contents were not available in the packaging workspace at bundle creation time.  
However, one key helper snippet was explicitly provided and should be reused:

```php
/**
 * @param $hostType
 * @param $forceCreate
 * @return Service|mixed
 */
private static function getService($hostType = 'webmaster', $forceCreate = false)
{
    static $services;

    if (is_null($services[$hostType]) || $forceCreate) {
        $services[$hostType] = new Service($hostType);
    }

    return $services[$hostType];
}
```

## 2. Product ID extraction from URLs
This is critical for Metrika imports.
Need to answer:
- Is product ID always present in card URLs?
- Is there a stable URL->product mapping table?
- Can product ID be derived from slug or XML_ID?

## 3. Exact recommendation surfaces
Main target is clear, but implementation detail still needed:
- product page block
- category/listing block
- homepage block
- should cart page be included in MVP?

## 4. Purchase signal availability
Need to validate:
- is Metrika e-commerce configured correctly?
- are add-to-cart / purchase events already transmitted to Metrika?
- if not, site-side events become even more important

## 5. Identity stitching policy
Open design question:
- when both internal anon ID and Metrika ClientID exist, what is the durable linking rule?
- do we keep mapping table `anon_id -> metrika_client_id`?

## 6. Recommendation ranking policy
Open choices for v1.1:
- should margin influence ranking?
- should in-stock products be boosted?
- should same-brand be boosted or penalized?
- how strong should price proximity be?

## 7. Infra choice
Still to choose:
- self-hosted ClickHouse vs managed ClickHouse
- same server vs separate VM for collector
- PHP collector vs Go collector

## 8. Data retention
Needs explicit business decision:
- raw events retention period
- aggregate retention period
- archive retention period

## Recommended immediate clarifications
1. confirm product URL -> product ID strategy
2. confirm available Metrika goals/e-commerce
3. confirm infra choice for ClickHouse/Redis
4. confirm first page surfaces for widget rollout
