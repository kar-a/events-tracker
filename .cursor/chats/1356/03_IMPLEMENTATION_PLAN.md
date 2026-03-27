# Implementation plan

## Phase 1 — Event foundation
### Step 1. Freeze unified event contract
Why:
- without it, web/metrika/mobile will produce incompatible data

Deliverables:
- event schema
- allowed event types
- validation and normalization rules

### Step 2. Implement collector on `data.trimiata.ru`
Why:
- all event sources need one ingestion door

Deliverables:
- `POST /v1/events`
- `POST /v1/events/batch`
- validation
- batching
- logging

### Step 3. Create ClickHouse raw tables
Why:
- raw append-only storage is the base for every later model

Deliverables:
- `events_raw`
- optional `ingest_errors`
- optional `import_jobs`

## Phase 2 — Live site data
### Step 4. Instrument site events
Track:
- `product_view`
- `add_to_cart`
- `remove_from_cart`
- `purchase`
- `search`
- `recommendation_show`
- `recommendation_click`

Why:
- these are the highest-value signals for MVP recommendations

### Step 5. Use Metrika ClientID as anonymous identity
Why:
- stable anonymous browser ID
- bridge between live site events and imported Metrika data

## Phase 3 — Historical enrichment
### Step 6. Implement Yandex Metrika Logs API importer
Why:
- do not wait months to build history from scratch
- use already accumulated data

Deliverables:
- create request
- poll status
- download parts
- parse rows
- normalize to event contract
- load into ClickHouse

## Phase 4 — Aggregation and MVP recommendation logic
### Step 7. Compute item-to-item relationships
Signals:
- co-view in same session
- co-cart in same session
- co-purchase

Why:
- strongest realistic MVP for current size

### Step 8. Build fallback lists
Fallback order:
1. same category + near price
2. popular in category
3. popular sitewide
4. seasonal/new arrivals if desired

## Phase 5 — Serving
### Step 9. Push top-N recommendations into Redis
Why:
- page requests must not compute recommendation math live

### Step 10. Add Bitrix endpoint and widget
Surfaces:
- product page
- category page
- homepage

## Phase 6 — Measurement
### Step 11. Add recommendation analytics
Track:
- show
- click
- add_to_cart after click
- purchase after click

### Step 12. Run A/B test
Why:
- recommendations are successful only if they improve business metrics

## Suggested first sprint
1. finalize event schema
2. implement collector
3. create ClickHouse DDL
4. start writing live web events
