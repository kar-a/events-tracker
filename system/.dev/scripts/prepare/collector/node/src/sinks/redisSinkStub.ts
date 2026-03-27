import type { FastifyBaseLogger } from 'fastify';
import type { NormalizedEvent } from '../domain/eventSchema.js';
import type { RedisSink } from './types.js';

export class RedisSinkStub implements RedisSink {
	private readonly logger: FastifyBaseLogger;
	// eslint-disable-next-line @typescript-eslint/no-unused-vars
	private readonly cfg: Readonly<{ url: string }>;

	public constructor(cfg: Readonly<{ url: string }>, logger: FastifyBaseLogger) {
		this.cfg = cfg;
		this.logger = logger;
	}

	public async touch(events: ReadonlyArray<NormalizedEvent>): Promise<void> {
		// Заглушка: позже можно писать "recent activity" или AB assignment.
		this.logger.debug({ count: events.length }, 'redis: stub touch');
	}
}

