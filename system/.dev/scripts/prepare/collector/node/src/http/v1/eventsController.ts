import type { FastifyReply, FastifyRequest } from 'fastify';
import { normalizeEvent, normalizeEventsInput, type NormalizedEvent } from '../../domain/eventSchema.js';
import type { ClickHouseSink, RedisSink } from '../../sinks/types.js';

export class EventsController {
	private readonly clickhouse: ClickHouseSink;
	private readonly redis: RedisSink;
	private readonly logger: FastifyRequest['log'];

	public constructor(
		deps: Readonly<{ clickhouse: ClickHouseSink; redis: RedisSink; logger: FastifyRequest['log'] }>
	) {
		this.clickhouse = deps.clickhouse;
		this.redis = deps.redis;
		this.logger = deps.logger;
	}

	public async ingest(req: FastifyRequest, reply: FastifyReply): Promise<void> {
		if (!req.body || typeof req.body !== 'object') {
			reply.code(422).send({
				ok: false,
				errors: [{ index: 0, error: 'Invalid JSON payload: expected object' }]
			});
			return;
		}

		if (Array.isArray(req.body)) {
			reply.code(422).send({
				ok: false,
				errors: [{ index: 0, error: 'Invalid JSON payload: expected object, got array' }]
			});
			return;
		}

		const normalized = normalizeEvent(req.body as Record<string, unknown>);

		await this.persist([normalized]);

		reply.send({ ok: true, accepted: 1 });
	}

	public async ingestBatch(req: FastifyRequest, reply: FastifyReply): Promise<void> {
		const { events, errors } = normalizeEventsInput(req.body);

		if (errors.length) {
			reply.code(422).send({ ok: false, errors });
			return;
		}

		if (events.length === 0) {
			reply.code(422).send({ ok: false, errors: [{ index: 0, error: 'Batch payload must not be empty' }] });
			return;
		}

		const normalized: NormalizedEvent[] = [];
		const normErrors: Array<{ index: number; error: string }> = [];

		for (let i = 0; i < events.length; i += 1) {
			try {
				normalized.push(normalizeEvent(events[i] as Record<string, unknown>));
			} catch (e) {
				normErrors.push({ index: i, error: e instanceof Error ? e.message : 'Unknown error' });
			}
		}

		if (normErrors.length) {
			reply.code(422).send({ ok: false, errors: normErrors });
			return;
		}

		await this.persist(normalized);

		reply.send({ ok: true, accepted: normalized.length });
	}

	private async persist(events: NormalizedEvent[]): Promise<void> {
		// ClickHouse write is the critical side-effect.
		await this.clickhouse.writeEvents(events);

		// Redis is optional in collector stage; keep as a stub for now.
		await this.redis.touch(events);

		this.logger.info({ accepted: events.length }, 'collector: accepted');
	}
}

