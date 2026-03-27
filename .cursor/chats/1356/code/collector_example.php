<?php

declare(strict_types=1);

use Trimiata\Data\Event\TrimiataEventSchema;

/**
 * Minimal collector example for data.trimiata.ru
 * This is intentionally simple: production version should add auth/rate-limits/logging/retries.
 */

require_once __DIR__ . '/TrimiataEventSchema.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON']);
    exit;
}

$events = isset($data[0]) ? $data : [$data];
$normalized = [];
$errors = [];

foreach ($events as $index => $event) {
    try {
        $normalized[] = TrimiataEventSchema::normalize((array)$event);
    } catch (Throwable $e) {
        $errors[] = [
            'index' => $index,
            'error' => $e->getMessage(),
        ];
    }
}

if ($errors) {
    http_response_code(422);
    echo json_encode([
        'ok' => false,
        'errors' => $errors,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/*
 * TODO:
 * 1. batch-insert into ClickHouse
 * 2. optional enqueue to broker later
 * 3. log request metadata
 */

echo json_encode([
    'ok' => true,
    'accepted' => count($normalized),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
