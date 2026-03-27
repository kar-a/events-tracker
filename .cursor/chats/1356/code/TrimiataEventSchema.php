<?php

declare(strict_types=1);

namespace Trimiata\Data\Event;

use InvalidArgumentException;

/**
 * Единый контракт событий для data.trimiata.ru
 *
 * Первая задача сервиса событий — заставить web / metrika / mobile / backend
 * присылать данные в одном формате. Иначе потом начинается зоопарк полей,
 * и аналитика превращается в археологию.
 */
final class TrimiataEventSchema
{
    public const SOURCE_WEB = 'web';
    public const SOURCE_METRIKA = 'metrika';
    public const SOURCE_APP = 'app';
    public const SOURCE_BACKEND = 'backend';

    public const EVENT_PRODUCT_VIEW = 'product_view';
    public const EVENT_ADD_TO_CART = 'add_to_cart';
    public const EVENT_REMOVE_FROM_CART = 'remove_from_cart';
    public const EVENT_PURCHASE = 'purchase';
    public const EVENT_SEARCH = 'search';
    public const EVENT_RECOMMENDATION_SHOW = 'recommendation_show';
    public const EVENT_RECOMMENDATION_CLICK = 'recommendation_click';

    /**
     * Обязательные поля для любого события.
     */
    private const REQUIRED_FIELDS = [
        'event_id',
        'source',
        'event_type',
        'event_time',
        'user_key',
        'session_key',
    ];

    /**
     * Поля, которые мы хотим видеть почти всегда, но допускаем null.
     */
    private const OPTIONAL_FIELDS = [
        'product_id',
        'context_type',
        'context_id',
        'page_url',
        'page_type',
        'referrer',
        'device_type',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'search_query',
        'position',
        'payload',
    ];

    public static function getAllowedSources(): array
    {
        return [
            self::SOURCE_WEB,
            self::SOURCE_METRIKA,
            self::SOURCE_APP,
            self::SOURCE_BACKEND,
        ];
    }

    public static function getAllowedEventTypes(): array
    {
        return [
            self::EVENT_PRODUCT_VIEW,
            self::EVENT_ADD_TO_CART,
            self::EVENT_REMOVE_FROM_CART,
            self::EVENT_PURCHASE,
            self::EVENT_SEARCH,
            self::EVENT_RECOMMENDATION_SHOW,
            self::EVENT_RECOMMENDATION_CLICK,
        ];
    }

    /**
     * Валидирует и нормализует входящее событие.
     *
     * @param array<string,mixed> $event
     * @return array<string,mixed>
     */
    public static function normalize(array $event): array
    {
        foreach (self::REQUIRED_FIELDS as $field) {
            if (!array_key_exists($field, $event) || $event[$field] === null || $event[$field] === '') {
                throw new InvalidArgumentException(sprintf('Field "%s" is required.', $field));
            }
        }

        $source = (string)$event['source'];
        if (!in_array($source, self::getAllowedSources(), true)) {
            throw new InvalidArgumentException(sprintf('Unsupported source "%s".', $source));
        }

        $eventType = (string)$event['event_type'];
        if (!in_array($eventType, self::getAllowedEventTypes(), true)) {
            throw new InvalidArgumentException(sprintf('Unsupported event_type "%s".', $eventType));
        }

        $normalized = [
            'event_id' => (string)$event['event_id'],
            'source' => $source,
            'event_type' => $eventType,
            'event_time' => self::normalizeDateTime((string)$event['event_time']),
            'user_key' => self::normalizeString($event['user_key']),
            'session_key' => self::normalizeString($event['session_key']),
            'product_id' => self::normalizeNullableInt($event['product_id'] ?? null),
            'context_type' => self::normalizeNullableString($event['context_type'] ?? null),
            'context_id' => self::normalizeNullableString($event['context_id'] ?? null),
            'page_url' => self::normalizeNullableString($event['page_url'] ?? null),
            'page_type' => self::normalizeNullableString($event['page_type'] ?? null),
            'referrer' => self::normalizeNullableString($event['referrer'] ?? null),
            'device_type' => self::normalizeNullableString($event['device_type'] ?? null),
            'utm_source' => self::normalizeNullableString($event['utm_source'] ?? null),
            'utm_medium' => self::normalizeNullableString($event['utm_medium'] ?? null),
            'utm_campaign' => self::normalizeNullableString($event['utm_campaign'] ?? null),
            'search_query' => self::normalizeNullableString($event['search_query'] ?? null),
            'position' => self::normalizeNullableInt($event['position'] ?? null),
            'payload' => self::normalizePayload($event['payload'] ?? []),
        ];

        self::assertEventSpecificFields($normalized);

        return $normalized;
    }

    /**
     * Возвращает пример события, с которым можно стартовать интеграцию фронта.
     *
     * @return array<string,mixed>
     */
    public static function getExampleProductViewEvent(): array
    {
        return [
            'event_id' => '01HTRIMIATAEXAMPLE0001',
            'source' => self::SOURCE_WEB,
            'event_type' => self::EVENT_PRODUCT_VIEW,
            'event_time' => '2026-03-10 12:00:00',
            'user_key' => 'metrika:1234567890123456789',
            'session_key' => 'web:session:abc123',
            'product_id' => 12345,
            'context_type' => 'product',
            'context_id' => '12345',
            'page_url' => 'https://trimiata.ru/catalog/koltsa/koltso-12345/',
            'page_type' => 'product',
            'referrer' => 'https://yandex.ru/',
            'device_type' => 'desktop',
            'utm_source' => 'yandex',
            'utm_medium' => 'organic',
            'utm_campaign' => null,
            'search_query' => null,
            'position' => null,
            'payload' => [
                'price' => 189900,
                'currency' => 'RUB',
                'category_id' => 17,
            ],
        ];
    }

    private static function assertEventSpecificFields(array $event): void
    {
        if (in_array($event['event_type'], [self::EVENT_PRODUCT_VIEW, self::EVENT_ADD_TO_CART, self::EVENT_REMOVE_FROM_CART, self::EVENT_PURCHASE], true)
            && empty($event['product_id'])
        ) {
            throw new InvalidArgumentException('Field "product_id" is required for product events.');
        }

        if ($event['event_type'] === self::EVENT_SEARCH && empty($event['search_query'])) {
            throw new InvalidArgumentException('Field "search_query" is required for search events.');
        }

        if (in_array($event['event_type'], [self::EVENT_RECOMMENDATION_SHOW, self::EVENT_RECOMMENDATION_CLICK], true)
            && empty($event['context_id'])
        ) {
            throw new InvalidArgumentException('Field "context_id" is required for recommendation events.');
        }
    }

    private static function normalizeDateTime(string $value): string
    {
        $timestamp = strtotime($value);
        if ($timestamp === false) {
            throw new InvalidArgumentException(sprintf('Invalid event_time "%s".', $value));
        }

        return gmdate('Y-m-d H:i:s', $timestamp);
    }

    private static function normalizeString(mixed $value): string
    {
        $value = trim((string)$value);
        if ($value === '') {
            throw new InvalidArgumentException('String value can not be empty.');
        }

        return $value;
    }

    private static function normalizeNullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string)$value);
        return $value === '' ? null : $value;
    }

    private static function normalizeNullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_numeric($value)) {
            throw new InvalidArgumentException(sprintf('Value "%s" must be numeric.', (string)$value));
        }

        return (int)$value;
    }

    /**
     * @param mixed $payload
     * @return array<string,mixed>
     */
    private static function normalizePayload(mixed $payload): array
    {
        if ($payload === null) {
            return [];
        }

        if (!is_array($payload)) {
            throw new InvalidArgumentException('Payload must be an array.');
        }

        return $payload;
    }
}
