#!/usr/bin/env bash
# ──────────────────────────────────────────────────────────────────────────────
# MicroMVC Installer
# Usage:
#   curl -fsSL https://raw.githubusercontent.com/db3net/MicroMVC/master/install.sh | bash -s myapp
#   — or —
#   bash install.sh myapp
# ──────────────────────────────────────────────────────────────────────────────

set -euo pipefail

APP_NAME="${1:-myapp}"
REPO="https://github.com/db3net/MicroMVC.git"
TEMP_DIR=$(mktemp -d)

echo "🚀 Creating MicroMVC project: ${APP_NAME}"

# Clone into temp dir (shallow, no history)
git clone --depth 1 --quiet "$REPO" "$TEMP_DIR"

# Copy starter to destination
cp -r "$TEMP_DIR/starter" "$APP_NAME"

# Clean up
rm -rf "$TEMP_DIR"

echo ""
echo "✅ Done! Your project is ready at ./${APP_NAME}"
echo ""
echo "   cd ${APP_NAME}"
echo "   php -S localhost:8080 -t public"
echo ""
echo "   Or Docker:"
echo "   docker build -t ${APP_NAME} ."
echo "   docker run -p 8080:8080 ${APP_NAME}"
echo ""
echo "   Then open http://localhost:8080"
echo ""
