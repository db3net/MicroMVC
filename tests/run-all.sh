#!/usr/bin/env bash
# ──────────────────────────────────────────────────────────────────────────────
# run-all.sh — Spin up MySQL + PostgreSQL, run all tests, tear down
# Author: dblack
# Email: dblack@db3.net
# Copyright (c) 2026 db3.net. All rights reserved.
# ──────────────────────────────────────────────────────────────────────────────

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$SCRIPT_DIR"

echo "==> Starting MySQL and PostgreSQL containers..."
docker compose -f docker-compose.test.yml up -d --wait

echo ""
echo "==> Running unit tests..."
vendor/bin/phpunit --testsuite Unit

echo ""
echo "==> Running integration tests..."
vendor/bin/phpunit --testsuite Integration

EXIT_CODE=$?

echo ""
echo "==> Tearing down containers..."
docker compose -f docker-compose.test.yml down -v

exit $EXIT_CODE
