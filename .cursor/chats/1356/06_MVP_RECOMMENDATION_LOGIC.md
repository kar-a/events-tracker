# MVP recommendation logic

## Goal
Build a first useful block **“Вам может понравиться”** quickly and realistically.

## Recommendation strategy for MVP
Use **item-to-item relationships** from behavior.

### Primary signals
1. products viewed in the same session
2. products added to cart in the same session
3. products purchased together

### Relative weight idea
- co-view = base weight
- co-cart = stronger weight
- co-purchase = strongest weight

Example:
- co-view weight = 1
- co-cart weight = 3
- co-purchase weight = 5

Exact values can be tuned later.

## Basic scoring idea
For each pair `(A, B)`:
- add weighted evidence based on sessions/users/actions
- apply freshness decay if desired
- optionally boost same category / similar price
- filter inactive / unavailable products

## Fallback strategy
When product or user has little data:
1. same category + similar price
2. popular in same category
3. globally popular products
4. optional seasonal/new products

## Surface-specific logic
### Product page
Primary context:
- current product ID
- return similar/relevant products for this product

### Category page
Primary context:
- recent user interests if available
- else popular in current category

### Homepage
Primary context:
- last viewed products / last viewed categories
- else best sellers or seasonal list

## Serving format
Bitrix should not calculate pair scores at request time.  
Serve precomputed recommendation IDs from Redis / cache.

## What comes after MVP
- user-personal reranking
- category affinity per user
- price affinity per user
- image similarity
- text embeddings for names/descriptions
- LightFM / ALS / item2vec / session models
