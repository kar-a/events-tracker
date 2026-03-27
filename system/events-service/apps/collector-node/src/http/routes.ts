import type { FastifyInstance } from 'fastify';
import { EventsController } from './v1/eventsController.js';
import type { ClickHouseSink, RedisSink } from '../sinks/types.js';

export async function registerRoutes(
	app: FastifyInstance,
	deps: Readonly<{ clickhouse: ClickHouseSink; redis: RedisSink }>
): Promise<void> {
	const controller = new EventsController({ clickhouse: deps.clickhouse, redis: deps.redis, logger: app.log });

	app.get('/health', async () => ({ ok: true }));
	app.get('/ready', async () => ({ ok: true }));

	app.post('/v1/events', async (req, reply) => controller.ingest(req, reply));
	app.post('/v1/events/batch', async (req, reply) => controller.ingestBatch(req, reply));
}

