import type { FastifyBaseLogger } from 'fastify';
import type { NormalizedEvent } from '@trimiata/events-contract';
import type { RedisSink } from './types.js';

type RedisConfig = Readonly<{ url: string }>;

export class RedisSinkStub implements RedisSink {
	private readonly cfg: RedisConfig;
	private readonly logger: FastifyBaseLogger;

	public constructor(cfg: RedisConfig, logger: FastifyBaseLogger) {
		this.cfg = cfg;
		this.logger = logger;
	}

	public async touch(events: ReadonlyArray<NormalizedEvent>): Promise<void> {
		if (!events.length) return;
		this.logger.debug({ count: events.length, url: this.cfg.url }, 'redis: stub touch');
	}
}

