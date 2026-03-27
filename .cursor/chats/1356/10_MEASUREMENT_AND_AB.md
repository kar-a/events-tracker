# Measurement and A/B testing

## Events to track
- recommendation_show
- recommendation_click
- add_to_cart
- purchase

## Derived metrics
- block CTR
- click -> add_to_cart rate
- click -> purchase rate
- revenue per exposed user
- average order value after recommendation click

## Minimal A/B setup
### Group A
- no recommendation block or old logic

### Group B
- new recommendation logic

Assignment:
- deterministic hash from stable anonymous key

## Why A/B is mandatory
A recommendation block is not successful because it "looks relevant".  
It is successful only if it improves business outcomes.

## Attribution basics
Record:
- which algorithm generated the block
- which products were shown
- clicked position
- clicked product ID
- downstream cart/purchase events for same user/session

## Recommended event payload additions
For recommendation_show:
- algorithm
- block_id
- context_product_id
- shown_product_ids

For recommendation_click:
- algorithm
- block_id
- context_product_id
- clicked_product_id
- clicked_position
