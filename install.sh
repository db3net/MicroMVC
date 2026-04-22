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

echo "🚀 Creating MicroMVC project"
if [ -t 0 ]; then
    printf "   Project name [%s]: " "$APP_NAME"
    read -r input
    APP_NAME="${input:-$APP_NAME}"
fi

# Clone into temp dir (shallow, no history)
git clone --depth 1 --quiet "$REPO" "$TEMP_DIR"

# Copy only the app files — skip dev/repo artifacts
mkdir -p "$APP_NAME"
cp -r "$TEMP_DIR/src"         "$APP_NAME/src"
cp -r "$TEMP_DIR/config"      "$APP_NAME/config"
cp -r "$TEMP_DIR/controllers" "$APP_NAME/controllers"
cp -r "$TEMP_DIR/models"      "$APP_NAME/models"
cp -r "$TEMP_DIR/public"      "$APP_NAME/public"
cp -r "$TEMP_DIR/views"       "$APP_NAME/views"
mkdir -p "$APP_NAME/data"
cp "$TEMP_DIR/Dockerfile"         "$APP_NAME/Dockerfile"
cp "$TEMP_DIR/Dockerfile.alpine"  "$APP_NAME/Dockerfile.alpine"
cp "$TEMP_DIR/.dockerignore"      "$APP_NAME/.dockerignore"

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
