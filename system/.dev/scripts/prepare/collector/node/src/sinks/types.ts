import type { NormalizedEvent } from '../domain/eventSchema.js';

export interface ClickHouseSink {
	writeEvents(events: ReadonlyArray<NormalizedEvent>): Promise<void>;
}

export interface RedisSink {
	touch(events: ReadonlyArray<NormalizedEvent>): Promise<void>;
}

