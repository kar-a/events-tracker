import type { FastifyBaseLogger } from 'fastify';
import type { NormalizedEvent } from '../domain/eventSchema.js';
import type { ClickHouseSink } from './types.js';

export class ClickHouseSinkStub implements ClickHouseSink {
	private readonly logger: FastifyBaseLogger;
	// eslint-disable-next-line @typescript-eslint/no-unused-vars
	private readonly cfg: Readonly<{ url: string; db: string; user: string; password: string }>;

	public constructor(
		cfg: Readonly<{ url: string; db: string; user: string; password: string }>,
		logger: FastifyBaseLogger
	) {
		this.cfg = cfg;
		this.logger = logger;
	}

	public async writeEvents(events: ReadonlyArray<NormalizedEvent>): Promise<void> {
		// Заглушка: следующая итерация — батч-вставка в ClickHouse (HTTP JSONEachRow / Native).
		// Важно: payload будет сериализован в payload_json и запись будет append-only.
		this.logger.debug({ count: events.length }, 'clickhouse: stub write');
	}
}

