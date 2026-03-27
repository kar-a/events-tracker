# Docker build

Dockerfile’ы приложений лежат **рядом с кодом**, чтобы build context был минимальным и предсказуемым:

| Сервис | Файл |
|--------|------|
| Node collector | `apps/collector-node/Dockerfile` |
| Python jobs (каркас) | `apps/jobs-python/Dockerfile` |

В `infra/compose/docker-compose.events.yml` указано:

```yaml
build:
  context: ../../
  dockerfile: apps/collector-node/Dockerfile
```

Дублировать те же Dockerfile в `infra/docker/` не делаем — один источник правды. При необходимости сюда можно добавить только **обёртки** или multi-stage общие слои.
