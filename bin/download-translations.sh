#!/bin/bash
# Translation Download Script Template
# This script downloads translations for a WP Statistics add-on from the bulk-export endpoint

# Configuration
SLUG="${1:-wp-statistics-data-plus}"  # Default to data-plus if no argument provided
LANGUAGES_DIR="languages"
BASE_URL="https://translations.veronalabs.com/bulk-export"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if slug is provided
if [ -z "$1" ]; then
    echo -e "${YELLOW}Usage: $0 <addon-slug>${NC}"
    echo -e "${YELLOW}Using default slug: ${SLUG}${NC}"
    echo ""
    echo "Available add-on slugs:"
    echo "  - wp-statistics-data-plus"
    echo "  - wp-statistics-marketing"
    echo "  - wp-statistics-mini-chart"
    echo "  - wp-statistics-advanced-reporting"
    echo "  - wp-statistics-realtime-stats"
    echo "  - wp-statistics-widgets"
    echo "  - wp-statistics-customization"
    echo "  - wp-statistics-rest-api"
    echo ""
fi

# Create languages directory if it doesn't exist
if [ ! -d "$LANGUAGES_DIR" ]; then
    mkdir -p "$LANGUAGES_DIR"
    echo -e "${GREEN}✓${NC} Created languages directory"
fi

# Set temporary file name
TMP_FILE="${SLUG}-translations.zip"

# Download the translations ZIP file
echo "Downloading translations for ${SLUG}..."
HTTP_CODE=$(curl -w "%{http_code}" -L -o "$TMP_FILE" "${BASE_URL}/${SLUG}/?format=mo" 2>/dev/null)

if [ "$HTTP_CODE" != "200" ]; then
    echo -e "${RED}✗ Failed to download translations: HTTP $HTTP_CODE${NC}"
    rm -f "$TMP_FILE"
    exit 1
fi

if [ ! -f "$TMP_FILE" ] || [ ! -s "$TMP_FILE" ]; then
    echo -e "${RED}✗ Failed to download translations: Empty or missing file${NC}"
    rm -f "$TMP_FILE"
    exit 1
fi

echo -e "${GREEN}✓${NC} Downloaded translations archive"

# Check if unzip is available
if ! command -v unzip &> /dev/null; then
    echo -e "${RED}✗ Failed to extract translations: unzip command not found${NC}"
    echo "Please install unzip: sudo apt-get install unzip"
    rm -f "$TMP_FILE"
    exit 1
fi

# Extract only .mo and .po files from the ZIP archive
echo "Extracting translation files..."
if unzip -o -j "$TMP_FILE" "*.mo" "*.po" -d "$LANGUAGES_DIR" 2>/dev/null; then
    echo -e "${GREEN}✓${NC} Extracted translation files to ${LANGUAGES_DIR}/"
    
    # List extracted files
    echo ""
    echo "Extracted files:"
    ls -lh "$LANGUAGES_DIR"/${SLUG}-*.mo "$LANGUAGES_DIR"/${SLUG}-*.po 2>/dev/null | awk '{print "  " $9 " (" $5 ")"}'
else
    echo -e "${RED}✗ Failed to extract translations: No translation files found in archive${NC}"
    rm -f "$TMP_FILE"
    exit 1
fi

# Clean up temporary ZIP file
rm -f "$TMP_FILE"
echo -e "\n${GREEN}✓${NC} Successfully downloaded and extracted translations for ${SLUG}"
