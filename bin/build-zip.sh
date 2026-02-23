#!/bin/bash
#
# build-zip.sh - Creates a clean distribution ZIP for a WordPress plugin
#
# Usage:
#   composer dist          # Via composer script
#   bash bin/build-zip.sh  # Direct execution
#
# This script runs the full build pipeline:
#   1. composer install --no-dev --optimize-autoloader
#   2. pnpm install (if package.json exists)
#   3. pnpm build (auto-detects build script from package.json)
#   4. Creates ZIP using .distignore
#

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLUGIN_DIR="$(dirname "$SCRIPT_DIR")"
PLUGIN_SLUG="$(basename "$PLUGIN_DIR")"
OUTPUT_DIR="$PLUGIN_DIR/dist"
BUILD_DIR="/tmp/wp-plugin-dist"

# Get version from main plugin file header
MAIN_PHP="$PLUGIN_DIR/${PLUGIN_SLUG}.php"
VERSION=""
if [ -f "$MAIN_PHP" ]; then
    VERSION=$(grep "Version:" "$MAIN_PHP" | head -1 | sed 's/.*Version:[[:space:]]*//' | sed 's/[^0-9.].*//')
fi

# Get current git branch name (sanitized for filenames)
BRANCH=$(git -C "$PLUGIN_DIR" rev-parse --abbrev-ref HEAD 2>/dev/null | sed 's/[^a-zA-Z0-9._-]/-/g')
if [ -z "$BRANCH" ] || [ "$BRANCH" = "HEAD" ]; then
    BRANCH="unknown"
fi

# Build ZIP filename
if [ -n "$VERSION" ]; then
    ZIP_NAME="${PLUGIN_SLUG}-${VERSION}-${BRANCH}.zip"
else
    ZIP_NAME="${PLUGIN_SLUG}.zip"
fi

if [ ! -f "$PLUGIN_DIR/.distignore" ]; then
    echo "Error: .distignore not found in $PLUGIN_DIR"
    exit 1
fi

cd "$PLUGIN_DIR"

# ---- Step 1: Install PHP dependencies ----
echo "Step 1: Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader
echo ""

# ---- Step 2: Install Node.js dependencies ----
if [ -f "$PLUGIN_DIR/package.json" ]; then
    echo "Step 2: Installing Node.js dependencies..."
    pnpm install
    echo ""

    # ---- Step 3: Build assets ----
    # Auto-detect the build command from package.json scripts
    BUILD_CMD=$(node -e "
        const s = require('./package.json').scripts || {};
        const cmds = ['build', 'build-assets', 'g', 'dev'];
        const found = cmds.find(c => s[c]);
        if (found) console.log(found);
    " 2>/dev/null)

    if [ -n "$BUILD_CMD" ]; then
        echo "Step 3: Building assets (pnpm $BUILD_CMD)..."
        pnpm "$BUILD_CMD"
        echo ""
    else
        echo "Step 3: Skipped (no build script found in package.json)"
        echo ""
    fi
else
    echo "Step 2: Skipped (no package.json found)"
    echo "Step 3: Skipped (no package.json found)"
    echo ""
fi

# ---- Step 4: Create distribution ZIP ----
echo "Step 4: Creating distribution ZIP..."

# Clean previous build
rm -rf "${BUILD_DIR:?}/$PLUGIN_SLUG"
mkdir -p "$BUILD_DIR/$PLUGIN_SLUG"
mkdir -p "$OUTPUT_DIR"

# Copy files, respecting .distignore
rsync -rc --exclude-from="$PLUGIN_DIR/.distignore" \
    "$PLUGIN_DIR/" "$BUILD_DIR/$PLUGIN_SLUG/" --delete

# Remove system files
find "$BUILD_DIR/$PLUGIN_SLUG" -name ".DS_Store" -delete 2>/dev/null || true
find "$BUILD_DIR/$PLUGIN_SLUG" -name "Thumbs.db" -delete 2>/dev/null || true

# Create ZIP
cd "$BUILD_DIR"
rm -f "$OUTPUT_DIR/$ZIP_NAME"
zip -rq "$OUTPUT_DIR/$ZIP_NAME" "$PLUGIN_SLUG/"

# Cleanup
rm -rf "${BUILD_DIR:?}/$PLUGIN_SLUG"

ZIP_SIZE=$(du -h "$OUTPUT_DIR/$ZIP_NAME" | cut -f1)
echo ""
echo "Created: $OUTPUT_DIR/$ZIP_NAME ($ZIP_SIZE)"
