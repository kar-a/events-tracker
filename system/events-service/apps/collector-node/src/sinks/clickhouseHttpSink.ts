import type { FastifyBaseLogger } from 'fastify';
import type { NormalizedEvent } from '@trimiata/events-contract';
import type { ClickHouseSink } from './types.js';

type ClickHouseConfig = Readonly<{ url: string; db: string; user: string; password: string }>;

export class ClickHouseHttpSink implements ClickHouseSink {
	private readonly cfg: ClickHouseConfig;
	private readonly logger: FastifyBaseLogger;

	public constructor(cfg: ClickHouseConfig, logger: FastifyBaseLogger) {
		this.cfg = cfg;
		this.logger = logger;
	}

	public async writeEvents(events: ReadonlyArray<NormalizedEvent>): Promise<void> {
		if (!events.length) return;

		const body = events.map((e) => JSON.stringify(this.toRow(e))).join('\n') + '\n';
		const query = `INSERT INTO ${this.cfg.db}.events_raw
(
  event_time, event_id, source, event_type,
  user_key, session_key,
  product_id, context_type, context_id,
  page_url, page_type, referrer, device_type,
  utm_source, utm_medium, utm_campaign,
  search_query, position,
  payload_json
) FORMAT JSONEachRow`;

		const url = this.buildClickHouseUrl(query);

		await this.withRetries(async () => {
			const res = await fetch(url, {
				method: 'POST',
				headers: { 'content-type': 'application/json' },
				body
			});

			if (!res.ok) {
				const text = await res.text().catch(() => '');
				throw new Error(`ClickHouse insert failed: ${res.status} ${res.statusText}${text ? `; ${text}` : ''}`);
			}
		});
	}

	private toRow(e: NormalizedEvent): Record<string, unknown> {
		return {
			event_time: e.event_time,
			event_id: e.event_id,
			source: e.source,
			event_type: e.event_type,
			user_key: e.user_key,
			session_key: e.session_key,
			product_id: e.product_id ?? 0,
			context_type: e.context_type,
			context_id: e.context_id,
			page_url: e.page_url,
			page_type: e.page_type,
			referrer: e.referrer,
			device_type: e.device_type,
			utm_source: e.utm_source,
			utm_medium: e.utm_medium,
			utm_campaign: e.utm_campaign,
			search_query: e.search_query,
			position: e.position,
			payload_json: JSON.stringify(e.payload ?? {})
		};
	}

	private buildClickHouseUrl(sql: string): string {
		const base = this.cfg.url.replace(/\/+$/, '');
		const u = new URL(base + '/');
		u.searchParams.set('query', sql);
		u.searchParams.set('user', this.cfg.user);
		u.searchParams.set('password', this.cfg.password);
		return u.toString();
	}

	private async withRetries(fn: () => Promise<void>): Promise<void> {
		const maxAttempts = 3;
		let lastError: unknown;

		for (let attempt = 1; attempt <= maxAttempts; attempt += 1) {
			try {
				await fn();
				return;
			} catch (e) {
				lastError = e;
				const isLast = attempt === maxAttempts;
				this.logger.warn(
					{ attempt, maxAttempts, err: e instanceof Error ? e.message : String(e) },
					'clickhouse: insert failed'
				);
				if (isLast) break;

				await sleepMs(200 * attempt * attempt);
			}
		}

		throw lastError instanceof Error ? lastError : new Error('ClickHouse insert failed');
	}
}

function sleepMs(ms: number): Promise<void> {
	return new Promise((resolve) => setTimeout(resolve, ms));
}

