import pino from 'pino';
import type { FastifyBaseLogger } from 'fastify';
import type { AppConfig } from './config.js';

export function buildLogger(config: AppConfig): FastifyBaseLogger {
	const isDev = config.env !== 'production';

	return pino(
		{
			level: isDev ? 'debug' : 'info',
			base: null
		},
		isDev
			? pino.transport({
					target: 'pino-pretty',
					options: { colorize: true, translateTime: 'SYS:standard' }
				})
			: undefined
	);
}

