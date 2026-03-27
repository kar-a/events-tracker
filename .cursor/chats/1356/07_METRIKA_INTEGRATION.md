# Yandex Metrika integration

## Why Metrika matters
Metrika has already been installed for a long time. That means it can provide historical behavior that the site itself may not have stored in a normalized event warehouse.

## Core decisions
- use **ClientID** as the main anonymous identity when available
- use **Logs API** for historical import
- normalize imported data into the same event contract as live website events

## Important known facts from official docs
- `getClientID` in the JS API returns the site user ID assigned by Metrika
- Logs API exports non-aggregated data
- Yandex docs explicitly say the retrieved data can be stored and managed in ClickHouse
- current-day stats in Logs API are not available because they may be incomplete
- Yandex recommends requesting the previous day and earlier
- on average, 99% of sessions conclude within 3 days of their start
- there is quota / storage management for prepared logs

## Practical implication
Metrika import is not the live event pipe.  
It is a historical and delayed enrichment source.

## Recommended importer flow
1. create log request
2. poll request status
3. download parts when ready
4. parse rows
5. map URL -> `product_id` where needed
6. convert rows to unified event schema
7. insert into ClickHouse
8. store import job metadata and archive files

## What to extract
### High-value fields
- client ID
- session / visit IDs if available
- event/session timestamps
- page URLs
- referrer
- device category
- UTM/source data
- product/cart/purchase info when available
- goal-related e-commerce signals if configured

## Identity policy
### Preferred
`user_key = "metrika:{clientID}"`

### If absent
fallback to internal anonymous key from site

## Implementation note for existing PHP codebase
The project already has Yandex integration classes.  
The Metrika integration should follow the same style and helper conventions used in that codebase, especially the existing `getService(...)` pattern.
