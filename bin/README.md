# WP Statistics Dummy Data Generator

Development tool for generating realistic test data for WP Statistics v15.

## Overview

This standalone tool generates realistic analytics data to help test and develop the WP Statistics v15 React dashboard. It creates visitors, sessions, views, and all necessary dimension data with realistic distributions and patterns.

## Features

- ✅ **Realistic traffic patterns** - Weekday/weekend variations, seasonal trends, growth simulation
- ✅ **Geographic diversity** - 20+ countries with city-level data
- ✅ **Device variety** - Desktop (60%), Mobile (35%), Tablet (5%) distributions
- ✅ **Browser diversity** - Chrome, Firefox, Safari, Edge, Opera with versions
- ✅ **Traffic sources** - Direct, search, social, referral, email, paid channels
- ✅ **Behavioral patterns** - Realistic bounce rates, session durations, pages per session
- ✅ **Customizable** - JSON configs for easy modification of distributions

## Installation

No installation needed. This is a standalone development tool included in the repository.

## Requirements

- WordPress installed and running
- WP Statistics plugin active
- PHP CLI access
- Some existing WordPress posts/pages (for resource association)

## Usage

### Quick Start

Generate 30 days of data with default settings:
```bash
./bin/dummy-v15.sh --days=30
```

### Common Commands

#### Generate full year of data
```bash
./bin/dummy-v15.sh --days=365
```

#### Custom date range
```bash
./bin/dummy-v15.sh --from=2024-01-01 --to=2024-12-31
```

#### Clean existing data and regenerate
```bash
./bin/dummy-v15.sh --days=90 --clean
```

#### Custom traffic volume
```bash
./bin/dummy-v15.sh --days=90 --visitors-per-day=500
```

#### Low traffic for quick testing
```bash
./bin/dummy-v15.sh --days=10 --visitors-per-day=50
```

### Available Options

| Option | Description | Default |
|--------|-------------|---------|
| `--days=<number>` | Number of days to generate | 365 |
| `--from=<YYYY-MM-DD>` | Start date | (calculated from days) |
| `--to=<YYYY-MM-DD>` | End date | Today |
| `--visitors-per-day=<number>` | Average visitors per day | 200 |
| `--clean` | Remove existing data before generating | false |
| `--help` | Show help message | - |

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

**Seasonal Variations:**
- Summer dip (July-August): 0.85x
- Fall peak (September-November): 1.15-1.2x
- Other months: 0.9-1.1x

**Growth Simulation:**
- Starts at 70% of baseline
- Grows to 130% by end of period
- Simulates organic site growth

### Geographic Distribution

**Top Countries (% of traffic):**
- United States: 35%
- United Kingdom: 10%
- Canada: 8%
- Germany: 6%
- France, Australia, India: 5% each
- 13 more countries: 1-4% each

Cities are randomly selected from realistic city lists for each country.

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

**Search Engines:**
- Google: 85%
- Bing: 10%
- Yahoo, DuckDuckGo: 5%

**Social Networks:**
- Facebook: 40%
- Twitter: 25%
- LinkedIn: 20%
- Instagram, Reddit: 15%

### User Behavior

**Pages Per Session:**
- 1 page (bounce): 40%
- 2 pages: 25%
- 3 pages: 15%
- 4 pages: 10%
- 5+ pages: 10%

**Session Duration:**
- Bounces: 0-30 seconds
- Multi-page: 30 seconds to 10 minutes
- Scales with page count

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
- Email sources
- Paid ad sources

## Performance

### Estimated Generation Times

| Days | Visitors/Day | Total Records | Time |
|------|--------------|---------------|------|
| 10 | 50 | ~2,000 | ~10 sec |
| 30 | 100 | ~10,000 | ~30 sec |
| 90 | 200 | ~60,000 | ~2 min |
| 365 | 200 | ~250,000 | ~8 min |

*Times are approximate and vary based on server performance.*

## Troubleshooting

### "WP Statistics plugin not found"
- Ensure WP Statistics is installed and activated
- Check that the script can load WordPress (wp-load.php path)

### Memory errors
- Reduce `--visitors-per-day` value
- Generate data in smaller batches
- Increase PHP memory limit: `php -d memory_limit=512M bin/generate-dummy-data.php`

### Slow performance
- Check database server performance
- Ensure adequate server resources
- Consider reducing data volume for testing

### No posts found
- Create some WordPress posts/pages first
- The generator needs existing content to associate views with

## Technical Details

### Database Tables Populated

- `visitors` - Unique visitor records
- `sessions` - Session records with device, location, referrer info
- `views` - Page view records linked to sessions
- `countries` - Geographic countries
- `cities` - Cities within countries
- `device_types` - Desktop, Mobile, Tablet
- `device_browsers` - Browser types
- `device_browser_versions` - Browser version strings
- `device_oss` - Operating systems
- `referrers` - Traffic source information
- `resources` - WordPress post/page metadata
- `resource_uris` - URL mappings for resources

### Implementation

- **Language**: PHP
- **Architecture**: Single standalone script
- **Dependencies**: WordPress, WP Statistics plugin
- **Database Access**: Via WP Statistics RecordFactory classes
- **Distributions**: JSON configuration files

## Notes

- ⚠️ **Development Only**: This tool is for development and testing. It is NOT included in plugin releases to wordpress.org.
- 📊 **Dashboard Testing**: Generated data is designed specifically to test the WP Statistics v15 React dashboard.
- 🔄 **Repeatable**: Use the same date range to regenerate consistent test scenarios.
- 🧹 **Cleanup**: Use `--clean` flag to remove test data and start fresh.

## Examples

### Quick UI Test (10 days)
```bash
./bin/dummy-v15.sh --days=10 --visitors-per-day=100
```

### Realistic Dashboard Test (90 days)
```bash
./bin/dummy-v15.sh --days=90 --visitors-per-day=200
```

### Performance Test (full year, high volume)
```bash
./bin/dummy-v15.sh --days=365 --visitors-per-day=500
```

### Clean and Regenerate Test Data
```bash
./bin/dummy-v15.sh --days=30 --clean --visitors-per-day=150
```

## Support

This is a development tool. For WP Statistics plugin support, visit [wp-statistics.com](https://wp-statistics.com).

---

**Version**: 1.0.0
**Last Updated**: December 2024
**Compatibility**: WP Statistics v15+
