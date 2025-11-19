# Translation Download Scripts

This directory contains utility scripts for WP Statistics.

## download-translations.sh

Downloads and extracts translations for WP Statistics add-ons from the translation server.

### Prerequisites

- `curl` - for downloading files
- `unzip` - for extracting ZIP archives

Install on Ubuntu/Debian:
```bash
sudo apt-get install curl unzip
```

### Usage

```bash
bash bin/download-translations.sh <addon-slug>
```

### Available Add-on Slugs

- `wp-statistics-data-plus`
- `wp-statistics-marketing`
- `wp-statistics-mini-chart`
- `wp-statistics-advanced-reporting`
- `wp-statistics-realtime-stats`
- `wp-statistics-widgets`
- `wp-statistics-customization`
- `wp-statistics-rest-api`

### Examples

Download translations for Data Plus add-on:
```bash
bash bin/download-translations.sh wp-statistics-data-plus
```

Download translations for Marketing add-on:
```bash
bash bin/download-translations.sh wp-statistics-marketing
```

### Output

The script will:
1. Create a `languages/` directory if it doesn't exist
2. Download the translation ZIP file from the translation server
3. Extract `.mo` and `.po` files to the `languages/` directory
4. Clean up the temporary ZIP file
5. Display a list of extracted translation files

### Error Handling

The script handles common errors:
- Network/download failures
- Missing `unzip` command
- Empty or invalid ZIP files
- No translation files in archive

## WP-CLI Alternative

If you're using WP-CLI within a WordPress installation, you can use the CLI command instead:

```bash
# Download all translations in bulk for an add-on
wp statistics download-translations wp-statistics-data-plus --bulk

# Download translations for a specific locale
wp statistics download-translations wp-statistics-data-plus --locale=fa_IR

# Force re-download even if translations exist
wp statistics download-translations wp-statistics-data-plus --bulk --force
```

See `wp help statistics download-translations` for more information.
