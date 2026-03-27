import { createApp } from './app/createApp.js';
import { buildConfig } from './app/config.js';
import { buildLogger } from './app/logger.js';

const config = buildConfig(process.env);
const logger = buildLogger(config);

const app = await createApp({ config, logger });

await app.listen({ host: config.http.host, port: config.http.port });
logger.info({ port: config.http.port }, 'collector: started');

