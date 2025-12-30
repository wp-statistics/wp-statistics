# WP Statistics Dummy Data Simulator

Generate test data for WP Statistics v15 by simulating HTTP requests to the tracker endpoint.

## Quick Start

```bash
cd wp-content/plugins/wp-statistics

# Simple: Generate 7 days of realistic traffic
php bin/dummy-tracker-simulator.php --days=7 --visitors-per-day=50

# Advanced: Stress test with 100K records
php bin/stress-test-simulator.php --target=100K --scenario=stress
```

## Two Commands

| Command | Use Case |
|---------|----------|
| `dummy-tracker-simulator.php` | Simple realistic traffic for development/demos |
| `stress-test-simulator.php` | Stress testing, security validation, edge cases |

---

## dummy-tracker-simulator.php

Simple, date-based traffic generation.

### Options

| Option | Description | Default |
|--------|-------------|---------|
| `--days=N` | Days to generate | 7 |
| `--from=DATE` | Start date (YYYY-MM-DD) | calculated |
| `--to=DATE` | End date | today |
| `--visitors-per-day=N` | Visitors per day | 50 |
| `--delay=MS` | Delay between requests | 50 |
| `--url=URL` | Custom site URL | auto |
| `--verbose` | Detailed output | false |
| `--dry-run` | No HTTP requests | false |

### Examples

```bash
# Week of data
php bin/dummy-tracker-simulator.php --days=7 --visitors-per-day=50

# Month of data
php bin/dummy-tracker-simulator.php --days=30 --visitors-per-day=100

# Custom date range
php bin/dummy-tracker-simulator.php --from=2024-01-01 --to=2024-03-31

# Test without requests
php bin/dummy-tracker-simulator.php --days=1 --dry-run --verbose
```

---

## stress-test-simulator.php

Advanced testing with parallel requests, invalid data, and attack payloads.

### Options

| Option | Description | Default |
|--------|-------------|---------|
| `--target=N` | Records to generate (supports K/M: 100K, 1M) | 1000 |
| `--workers=N` | Parallel HTTP workers | 10 |
| `--scenario=NAME` | Preset: normal, stress, invalid, security, mixed | normal |
| `--invalid-ratio=N` | Invalid data ratio (0.0-1.0) | 0.0 |
| `--attack-ratio=N` | Attack payload ratio (0.0-1.0) | 0.0 |
| `--logged-in-ratio=N` | Logged-in visitor ratio | 0.12 |
| `--days=N` | Date range | 30 |
| `--resume` | Resume from checkpoint | false |
| `--checkpoint-id=ID` | Checkpoint name | auto |

### Scenarios

| Scenario | Invalid | Attack | Description |
|----------|---------|--------|-------------|
| `normal` | 0% | 0% | Realistic traffic |
| `stress` | 0% | 0% | High-volume (100K+) |
| `invalid` | 50% | 0% | Edge cases, malformed data |
| `security` | 0% | 20% | Attack payload testing |
| `mixed` | 10% | 5% | All data types |

### Examples

```bash
# Stress test
php bin/stress-test-simulator.php --target=100K --scenario=stress --workers=20

# Security testing
php bin/stress-test-simulator.php --target=5000 --scenario=security

# Custom ratios
php bin/stress-test-simulator.php --target=10K --invalid-ratio=0.1 --attack-ratio=0.05

# Resume interrupted job
php bin/stress-test-simulator.php --resume --checkpoint-id=my-test
```

---

## Data Patterns

### Traffic Distribution

- **Devices**: Desktop 60%, Mobile 35%, Tablet 5%
- **Browsers**: Chrome 65%, Firefox 15%, Safari 12%, Edge 6%
- **Countries**: US 35%, UK 10%, CA 8%, DE 6%, others distributed
- **Referrers**: Direct 40%, Search 30%, Social 15%, Referral 10%

### Weekday Multipliers

Mon 1.0x, Tue 1.1x, Wed 1.2x (peak), Thu 1.1x, Fri 0.9x, Sat 0.6x, Sun 0.5x

---

## Requirements

- WordPress with WP Statistics active
- PHP CLI access
- Web server running (for HTTP requests)

Settings auto-configured:
- `bypass_ad_blockers` = enabled
- `use_cache_plugin` = enabled

---

## Troubleshooting

**"Cannot connect to site"**
- Ensure web server is running
- Use `--url=https://yoursite.test` for custom URL
- Use `--dry-run` to test without requests

**"No posts found"**
- Create WordPress posts/pages first
- stress-test-simulator auto-creates sample content

**High failure rate**
- Check WP Statistics is activated
- Verify settings are configured

---

## File Structure

```
bin/
├── dummy-tracker-simulator.php   # Simple simulator
├── stress-test-simulator.php     # Advanced simulator
├── data/                         # JSON data files
│   ├── countries.json
│   ├── devices.json
│   ├── referrers.json
│   ├── invalid-data.json
│   └── attack-payloads.json
└── simulator/                    # Classes (stress-test only)
    ├── SimulatorRunner.php
    ├── Generators/
    └── Http/
```

---

**Compatibility**: WP Statistics v15+
