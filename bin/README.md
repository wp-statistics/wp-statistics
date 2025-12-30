# WP Statistics Tracker Simulator

Development tool for generating realistic test data for WP Statistics v15 by simulating actual Tracker.js requests.

## Overview

This tool generates realistic analytics data by making HTTP requests to `admin-ajax.php`, exactly like the frontend Tracker.js does. This ensures the full tracking pipeline is exercised (validation, signature checking, device detection, geolocation, etc.).

## Features

- **Full pipeline testing** - Data goes through the complete tracking flow
- **Realistic visitor profiles** - Different browsers, devices, countries, IPs
- **Geographic diversity** - 20+ countries with timezone/language mappings
- **Device variety** - Desktop (60%), Mobile (35%), Tablet (5%) distributions
- **Browser diversity** - Chrome, Firefox, Safari, Edge, Opera with real User-Agent strings
- **Traffic sources** - Direct, search, social, referral, email, paid channels
- **Customizable** - JSON configs for easy modification of distributions

## Requirements

- WordPress installed and running with web server accessible
- WP Statistics plugin active
- PHP CLI access
- **`use_cache_plugin` option enabled** (Client-side tracking)
- **`bypass_ad_blockers` option enabled** (AJAX tracking mode)
- Some existing WordPress posts/pages

## Usage

### Quick Start

Generate 7 days of data with default settings:
```bash
php bin/dummy-tracker-simulator.php --days=7 --visitors-per-day=50
```

### Common Commands

#### Dry run (test without HTTP requests)
```bash
php bin/dummy-tracker-simulator.php --days=7 --visitors-per-day=50 --dry-run
```

#### Verbose output
```bash
php bin/dummy-tracker-simulator.php --days=7 --visitors-per-day=50 --verbose
```

#### Custom date range
```bash
php bin/dummy-tracker-simulator.php --from=2024-12-01 --to=2024-12-31
```

#### Custom site URL (for non-standard setups)
```bash
php bin/dummy-tracker-simulator.php --url=https://mysite.local --days=7
```

#### Low traffic for quick testing
```bash
php bin/dummy-tracker-simulator.php --days=1 --visitors-per-day=10 --verbose
```

### Available Options

| Option | Description | Default |
|--------|-------------|---------|
| `--days=<number>` | Number of days to generate | 7 |
| `--from=<YYYY-MM-DD>` | Start date | (calculated from days) |
| `--to=<YYYY-MM-DD>` | End date | Today |
| `--visitors-per-day=<number>` | Average visitors per day | 50 |
| `--delay=<ms>` | Delay between requests in milliseconds | 50 |
| `--url=<url>` | Custom site URL | (from WordPress) |
| `--verbose` | Show detailed request output | false |
| `--dry-run` | Generate data without sending requests | false |
| `--help` | Show help message | - |

## How It Works

The simulator:
1. Loads WordPress and WP Statistics
2. Prepares resources (posts/pages) with `resource_uri_id` mappings
3. For each simulated visitor:
   - Generates a realistic visitor profile (browser, device, country, IP)
   - Creates a User-Agent string matching the device/browser/OS
   - Generates a valid signature using `wp_salt()`
   - Base64 encodes resource URI and referrer (like Tracker.js)
   - Sends HTTP POST to `admin-ajax.php` with `X-Forwarded-For` header for IP simulation

## Data Characteristics

### Traffic Patterns

**Weekday Variations:**
- Monday: 1.0x
- Tuesday: 1.1x
- Wednesday: 1.2x (peak)
- Thursday: 1.1x
- Friday: 0.9x
- Saturday: 0.6x
- Sunday: 0.5x

**Hour Distribution:**
- Business hours (9-5): Higher weight
- Off hours: Lower weight

### Geographic Distribution

**Top Countries (% of traffic):**
- United States: 35%
- United Kingdom: 10%
- Canada: 8%
- Germany: 6%
- France, Australia, India: 5% each
- 13 more countries: 1-4% each

Each country has mapped timezones and languages for realistic browser locale data.

### Device & Browser Distribution

**Device Types:**
- Desktop: 60%
- Mobile: 35%
- Tablet: 5%

**Browsers:**
- Chrome: 65%
- Firefox: 15%
- Safari: 12%
- Edge: 6%
- Opera: 2%

**Operating Systems** (matched to device type):
- Desktop: Windows 70%, macOS 20%, Linux 10%
- Mobile: Android 70%, iOS 30%
- Tablet: iOS 60%, Android 40%

### Traffic Sources

**Referrer Channels:**
- Direct: 40%
- Search: 30%
- Social: 15%
- Referral: 10%
- Email: 3%
- Paid: 2%

## Customization

Edit JSON files in `bin/data/` to customize distributions:

### `countries.json`
- Country weight distribution
- City lists per country
- Continent mappings

### `devices.json`
- Device type distribution
- Browser distribution
- OS distribution per device type
- Browser versions
- Screen resolutions

### `referrers.json`
- Channel distribution
- Search engine list with weights
- Social network list with weights
- Referral site list

### `user-agents.json`
- User-Agent string templates per device/OS/browser

### `timezones.json`
- IANA timezone mappings per country

### `languages.json`
- Browser language codes per country

## Troubleshooting

### "Cannot connect to site"
- Ensure your web server is running (MAMP, Valet, Laravel Herd, etc.)
- Use `--url=<your-url>` to specify the correct site URL
- Try `--dry-run` to test without HTTP requests

### "HTTP 301 Moved Permanently"
- Your site may redirect HTTP to HTTPS
- Use `--url=https://yoursite.test` with the correct protocol

### "bypass_ad_blockers not enabled"
- Enable "Bypass Ad Blockers" in WP Statistics settings, or
- The script will still attempt to work but may fail

### No posts found
- Create some WordPress posts/pages first
- The generator needs existing content to associate views with

### Slow performance
- Increase `--delay` between requests
- Reduce `--visitors-per-day`

## Technical Details

### Request Parameters Sent

The simulator sends these parameters (matching Tracker.js format):

| Parameter | Description |
|-----------|-------------|
| `action` | `wp_statistics_hit_record` |
| `resourceUriId` | ID from `resource_uris` table |
| `resourceUri` | Base64 encoded page path |
| `resource_type` | post, page, etc. |
| `resource_id` | WordPress post ID |
| `signature` | MD5 signature for validation |
| `timezone` | IANA timezone (e.g., "America/New_York") |
| `language` | Browser language code (e.g., "en-US") |
| `languageFullName` | Full language name (e.g., "English") |
| `screenWidth` | Screen width |
| `screenHeight` | Screen height |
| `referred` | Base64 encoded referrer URL |

### HTTP Headers

- `Content-Type: application/x-www-form-urlencoded`
- `User-Agent:` Realistic browser User-Agent string
- `X-Forwarded-For:` Simulated IP address for visitor
- `Referer:` Referrer URL

## Notes

- **Development Only**: This tool is for development and testing only.
- **Dashboard Testing**: Generated data tests the full WP Statistics v15 tracking pipeline.
- **Realistic Simulation**: Unlike direct database insertion, this exercises actual validation and processing.

## Examples

### Quick Test (1 day, 10 visitors)
```bash
php bin/dummy-tracker-simulator.php --days=1 --visitors-per-day=10 --verbose
```

### Week of Data (moderate volume)
```bash
php bin/dummy-tracker-simulator.php --days=7 --visitors-per-day=50
```

### Month of Data
```bash
php bin/dummy-tracker-simulator.php --days=30 --visitors-per-day=100
```

### Test Script Without Requests
```bash
php bin/dummy-tracker-simulator.php --days=7 --visitors-per-day=50 --dry-run --verbose
```

---

**Version**: 1.0.0
**Last Updated**: December 2024
**Compatibility**: WP Statistics v15+
