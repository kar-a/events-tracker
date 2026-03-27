# Redis keys and serving strategy

## Goal
Page rendering must read ready-made recommendation lists, not calculate them live.

## Suggested key patterns
### Product recommendations
- `rec:product:{product_id}` -> sorted list / JSON list of recommended product IDs

### User recommendations
- `rec:user:{user_key}` -> optional personalized top-N

### Category fallback
- `rec:category:{category_id}` -> popular/relevant in category

### Global fallback
- `rec:global:bestsellers`

## Suggested value shape
JSON example:
```json
{
  "generated_at": "2026-03-27T08:00:00Z",
  "algorithm": "item_to_item_v1",
  "items": [
    {"product_id": 101, "score": 0.912, "reason": "co_view"},
    {"product_id": 205, "score": 0.873, "reason": "co_cart"},
    {"product_id": 311, "score": 0.851, "reason": "same_category_price"}
  ]
}
```

## Serving decision tree
### Product page
1. try `rec:product:{current_product_id}`
2. fallback `rec:category:{category_id}`
3. fallback `rec:global:bestsellers`

### Homepage
1. try `rec:user:{user_key}`
2. fallback from recent categories
3. fallback global list

### Category page
1. try user-specific category recommendations
2. fallback category-level list

## Cache invalidation
- recommendations regenerated on schedule
- Redis keys overwritten atomically
- keep `generated_at` and `algorithm` in payload
