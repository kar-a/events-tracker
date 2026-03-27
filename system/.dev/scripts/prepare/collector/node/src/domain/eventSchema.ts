import { z } from 'zod';

const AllowedSources = ['web', 'metrika', 'app', 'backend'] as const;
const AllowedEventTypes = [
	'product_view',
	'add_to_cart',
	'remove_from_cart',
	'purchase',
	'search',
	'recommendation_show',
	'recommendation_click'
] as const;

const BaseEventSchema = z.object({
	event_id: z.string().min(1),
	source: z.enum(AllowedSources),
	event_type: z.enum(AllowedEventTypes),
	event_time: z.string().min(1),
	user_key: z.string().min(1),
	session_key: z.string().min(1),

	product_id: z.union([z.number().int().nonnegative(), z.string().min(1)]).optional().nullable(),
	context_type: z.string().optional().nullable(),
	context_id: z.string().optional().nullable(),
	page_url: z.string().optional().nullable(),
	page_type: z.string().optional().nullable(),
	referrer: z.string().optional().nullable(),
	device_type: z.string().optional().nullable(),
	utm_source: z.string().optional().nullable(),
	utm_medium: z.string().optional().nullable(),
	utm_campaign: z.string().optional().nullable(),
	search_query: z.string().optional().nullable(),
	position: z.union([z.number().int(), z.string()]).optional().nullable(),
	payload: z.record(z.string(), z.any()).optional().nullable()
});

export type NormalizedEvent = Readonly<{
	event_id: string;
	source: (typeof AllowedSources)[number];
	event_type: (typeof AllowedEventTypes)[number];
	event_time: string; // UTC 'YYYY-MM-DD HH:mm:ss'
	user_key: string;
	session_key: string;
	product_id: number | null;
	context_type: string | null;
	context_id: string | null;
	page_url: string | null;
	page_type: string | null;
	referrer: string | null;
	device_type: string | null;
	utm_source: string | null;
	utm_medium: string | null;
	utm_campaign: string | null;
	search_query: string | null;
	position: number | null;
	payload: Record<string, unknown>;
}>;

export function normalizeEventsInput(
	body: unknown
): { events: unknown[]; errors: Array<{ index: number; error: string }> } {
	if (Array.isArray(body)) return { events: body, errors: [] };
	if (body && typeof body === 'object') return { events: [body], errors: [] };

	return { events: [], errors: [{ index: 0, error: 'Invalid JSON payload: expected object or array' }] };
}

export function normalizeEvent(input: Record<string, unknown>): NormalizedEvent {
	const parsed = BaseEventSchema.parse(input);

	const normalized: NormalizedEvent = {
		event_id: parsed.event_id.trim(),
		source: parsed.source,
		event_type: parsed.event_type,
		event_time: normalizeUtcDateTime(parsed.event_time),
		user_key: parsed.user_key.trim(),
		session_key: parsed.session_key.trim(),
		product_id: normalizeNullableInt(parsed.product_id),
		context_type: normalizeNullableString(parsed.context_type),
		context_id: normalizeNullableString(parsed.context_id),
		page_url: normalizeNullableString(parsed.page_url),
		page_type: normalizeNullableString(parsed.page_type),
		referrer: normalizeNullableString(parsed.referrer),
		device_type: normalizeNullableString(parsed.device_type),
		utm_source: normalizeNullableString(parsed.utm_source),
		utm_medium: normalizeNullableString(parsed.utm_medium),
		utm_campaign: normalizeNullableString(parsed.utm_campaign),
		search_query: normalizeNullableString(parsed.search_query),
		position: normalizeNullableInt(parsed.position),
		payload: normalizePayload(parsed.payload)
	};

	assertEventSpecificFields(normalized);

	return normalized;
}

function assertEventSpecificFields(event: NormalizedEvent): void {
	const productEvents = new Set(['product_view', 'add_to_cart', 'remove_from_cart', 'purchase']);
	const recEvents = new Set(['recommendation_show', 'recommendation_click']);

	if (productEvents.has(event.event_type) && !event.product_id) {
		throw new Error('Field "product_id" is required for product events.');
	}

	if (event.event_type === 'search' && !event.search_query) {
		throw new Error('Field "search_query" is required for search events.');
	}

	if (recEvents.has(event.event_type) && !event.context_id) {
		throw new Error('Field "context_id" is required for recommendation events.');
	}
}

function normalizePayload(payload: unknown): Record<string, unknown> {
	if (!payload) return {};
	if (typeof payload !== 'object' || Array.isArray(payload)) throw new Error('Payload must be an object.');

	return payload as Record<string, unknown>;
}

function normalizeNullableString(value: unknown): string | null {
	if (value === null || value === undefined) return null;
	const s = String(value).trim();
	return s === '' ? null : s;
}

function normalizeNullableInt(value: unknown): number | null {
	if (value === null || value === undefined || value === '') return null;
	const n = typeof value === 'number' ? value : Number(value);
	if (!Number.isFinite(n)) throw new Error(`Value "${String(value)}" must be numeric.`);
	return Math.trunc(n);
}

function normalizeUtcDateTime(value: string): string {
	const ms = Date.parse(value);
	if (!Number.isFinite(ms)) throw new Error(`Invalid event_time "${value}".`);

	const d = new Date(ms);
	const pad = (x: number) => String(x).padStart(2, '0');

	// UTC "YYYY-MM-DD HH:mm:ss"
	return `${d.getUTCFullYear()}-${pad(d.getUTCMonth() + 1)}-${pad(d.getUTCDate())} ${pad(d.getUTCHours())}:${pad(
		d.getUTCMinutes()
	)}:${pad(d.getUTCSeconds())}`;
}

