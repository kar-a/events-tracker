<?php

declare(strict_types=1);

/**
 * Example Bitrix-side recommendation endpoint shape.
 * Replace Redis access and product loading with project-specific implementation.
 */

header('Content-Type: application/json; charset=utf-8');

$productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$userKey = isset($_GET['user_key']) ? (string)$_GET['user_key'] : '';

if ($productId <= 0 && $categoryId <= 0 && $userKey === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'No recommendation context']);
    exit;
}

/*
 * Pseudo decision tree:
 * 1. product recommendations
 * 2. user recommendations
 * 3. category fallback
 * 4. global fallback
 */

$result = [
    'ok' => true,
    'algorithm' => 'item_to_item_v1',
    'context' => [
        'product_id' => $productId,
        'category_id' => $categoryId,
        'user_key' => $userKey,
    ],
    'items' => [
        ['product_id' => 101, 'score' => 0.91, 'reason' => 'co_view'],
        ['product_id' => 205, 'score' => 0.88, 'reason' => 'co_cart'],
        ['product_id' => 311, 'score' => 0.81, 'reason' => 'same_category_price'],
    ],
];

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
