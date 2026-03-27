# Events stack: обёртка над infra/compose (не требует include в корневом docker-compose).
COMPOSE := docker compose -f infra/compose/docker-compose.events.yml
ENV   := infra/compose/.env

.PHONY: help env up down ps logs-grafana logs-collector build-contract test-contract init-ch prepare-host healthcheck demo-insert backup

help:
	@echo "Targets: env up down ps logs-grafana logs-collector build-contract test-contract init-ch prepare-host healthcheck demo-insert backup"
	@echo "Создайте $(ENV) из infra/compose/.env.example перед первым запуском."

env:
	@test -f $(ENV) || (echo "Создайте $(ENV): cp infra/compose/.env.example $(ENV)" && exit 1)

up: env
	$(COMPOSE) --env-file $(ENV) up -d --build

down: env
	$(COMPOSE) --env-file $(ENV) down

ps: env
	$(COMPOSE) --env-file $(ENV) ps

logs-grafana: env
	$(COMPOSE) --env-file $(ENV) logs -f --tail=200 grafana

logs-collector: env
	$(COMPOSE) --env-file $(ENV) logs -f --tail=200 collector

build-contract:
	cd packages/contract/node && npm install && npm run build

test-contract:
	cd packages/contract/node && npm install && npm run test

init-ch: env
	bash scripts/init-clickhouse.sh

prepare-host:
	bash scripts/00_prepare_host.sh

healthcheck: env
	bash scripts/30_healthcheck.sh

demo-insert: env
	bash scripts/40_demo_insert.sh

backup: env
	bash scripts/50_backup.sh
