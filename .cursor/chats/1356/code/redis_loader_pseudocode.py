import json
from collections import defaultdict

def build_top_n(pairs_rows, top_n=12):
    grouped = defaultdict(list)
    for row in pairs_rows:
        grouped[row["product_id"]].append({
            "product_id": row["related_product_id"],
            "score": row["score"],
            "reason": row["reason"],
        })
    result = {}
    for product_id, items in grouped.items():
        items.sort(key=lambda x: x["score"], reverse=True)
        result[product_id] = {
            "algorithm": "item_to_item_v1",
            "items": items[:top_n],
        }
    return result

def write_to_redis(redis_client, product_payloads):
    for product_id, payload in product_payloads.items():
        redis_client.set(
            f"rec:product:{product_id}",
            json.dumps(payload, ensure_ascii=False),
            ex=60 * 60 * 24,
        )
