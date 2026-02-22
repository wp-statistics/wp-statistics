#!/bin/bash
#
# build-zip.sh - Creates a clean distribution ZIP for a WordPress plugin
#
# Usage:
#   composer dist          # Via composer script
#   bash bin/build-zip.sh  # Direct execution
#
# Prerequisites:
#   - .distignore file in the plugin root
#   - composer install --no-dev (already run)
#   - npm run build or equivalent (already run)
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
echo "Created: $OUTPUT_DIR/$ZIP_NAME ($ZIP_SIZE)"
