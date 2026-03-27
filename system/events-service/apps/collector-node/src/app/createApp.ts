import Fastify, { type FastifyInstance } from 'fastify';
import type { AppConfig } from './config.js';
import { registerRoutes } from '../http/routes.js';
import { ClickHouseHttpSink } from '../sinks/clickhouseHttpSink.js';
import { RedisSinkStub } from '../sinks/redisSinkStub.js';
import type { FastifyBaseLogger } from 'fastify';

export type AppDeps = Readonly<{
	config: AppConfig;
	logger: FastifyBaseLogger;
}>;

export async function createApp(deps: AppDeps): Promise<FastifyInstance> {
	const app = Fastify({
		logger: deps.logger,
		bodyLimit: 2 * 1024 * 1024
	});

	app.decorate('config', deps.config);

	const clickhouse = new ClickHouseHttpSink(deps.config.clickhouse, deps.logger);
	const redis = new RedisSinkStub(deps.config.redis, deps.logger);

	await registerRoutes(app, { clickhouse, redis });

	return app;
}

