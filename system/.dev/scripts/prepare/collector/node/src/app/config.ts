import { z } from 'zod';

const EnvSchema = z.object({
	TZ: z.string().optional(),
	NODE_ENV: z.string().optional(),

	HTTP_HOST: z.string().optional().default('0.0.0.0'),
	HTTP_PORT: z.coerce.number().int().min(1).max(65535).optional().default(8080),

	CLICKHOUSE_DB: z.string().min(1),
	CLICKHOUSE_USER: z.string().min(1),
	CLICKHOUSE_PASSWORD: z.string().min(1),
	CLICKHOUSE_HTTP_URL: z.string().optional().default('http://clickhouse:8123'),

	REDIS_URL: z.string().optional().default('redis://redis:6379')
});

export type AppConfig = Readonly<{
	env: string;
	http: { host: string; port: number };
	clickhouse: { url: string; db: string; user: string; password: string };
	redis: { url: string };
}>;

export function buildConfig(env: NodeJS.ProcessEnv): AppConfig {
	const parsed = EnvSchema.parse(env);

	return {
		env: parsed.NODE_ENV ?? 'production',
		http: { host: parsed.HTTP_HOST, port: parsed.HTTP_PORT },
		clickhouse: {
			url: parsed.CLICKHOUSE_HTTP_URL,
			db: parsed.CLICKHOUSE_DB,
			user: parsed.CLICKHOUSE_USER,
			password: parsed.CLICKHOUSE_PASSWORD
		},
		redis: { url: parsed.REDIS_URL }
	};
}

