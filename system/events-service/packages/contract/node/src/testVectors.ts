import { readFile, readdir } from 'node:fs/promises';
import { join } from 'node:path';
import { normalizeEvent } from './zod.js';

const root = new URL('../../tests/vectors/', import.meta.url);

async function readJsonFiles(dir: URL): Promise<Array<{ name: string; json: unknown }>> {
	const names = await readdir(dir);
	const jsonNames = names.filter((n) => n.endsWith('.json'));
	return Promise.all(
		jsonNames.map(async (name) => {
			const raw = await readFile(new URL(name, dir), 'utf-8');
			return { name, json: JSON.parse(raw) };
		})
	);
}

async function main(): Promise<void> {
	const validDir = new URL('valid/', root);
	const invalidDir = new URL('invalid/', root);

	const valid = await readJsonFiles(validDir);
	const invalid = await readJsonFiles(invalidDir);

	for (const v of valid) {
		try {
			if (typeof v.json !== 'object' || !v.json || Array.isArray(v.json)) throw new Error('vector is not object');
			normalizeEvent(v.json as Record<string, unknown>);
		} catch (e) {
			throw new Error(`valid vector failed: ${v.name}: ${e instanceof Error ? e.message : String(e)}`);
		}
	}

	for (const v of invalid) {
		let ok = false;
		try {
			if (typeof v.json !== 'object' || !v.json || Array.isArray(v.json)) throw new Error('vector is not object');
			normalizeEvent(v.json as Record<string, unknown>);
		} catch {
			ok = true;
		}
		if (!ok) throw new Error(`invalid vector unexpectedly passed: ${v.name}`);
	}

	// eslint-disable-next-line no-console
	console.log(
		JSON.stringify(
			{
				valid: valid.length,
				invalid: invalid.length,
				status: 'ok',
				vectorsRoot: join(new URL('.', validDir).pathname)
			},
			null,
			2
		)
	);
}

await main();

