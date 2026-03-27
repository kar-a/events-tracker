#!/usr/bin/env bash
set -euo pipefail
# Алиас к dev-up.sh (единая точка запуска стека).
exec "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/dev-up.sh"
