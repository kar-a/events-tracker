import type { NormalizedEvent } from '@trimiata/events-contract';

export interface ClickHouseSink {
	writeEvents(events: ReadonlyArray<NormalizedEvent>): Promise<void>;
}

export interface RedisSink {
	touch(events: ReadonlyArray<NormalizedEvent>): Promise<void>;
}

