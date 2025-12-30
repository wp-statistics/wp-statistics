# WP Statistics Stress Test Simulator

A comprehensive dummy data generator for WP Statistics v15 that supports stress testing, invalid data handling, and security vulnerability testing.

## Overview

The simulator generates realistic HTTP traffic to the WP Statistics tracker endpoint, allowing you to:

- **Stress test** the system with configurable volumes (100K to 10M+ records)
- **Test invalid data handling** with edge cases, boundary values, and malformed data
- **Validate security** with OWASP attack payloads (SQL injection, XSS, etc.)
- **Simulate realistic traffic patterns** with proper device/browser correlation, time-zone aligned visits, and logged-in vs guest visitors

## Quick Start

```bash
# Navigate to plugin directory
cd wp-content/plugins/wp-statistics

# Generate 1000 realistic records
php bin/stress-test-simulator.php --target=1000

# Quick test (10 requests)
php bin/stress-test-simulator.php --quick
```

## Two Simulators: Which to Use?

| Command | Purpose | Best For |
|---------|---------|----------|
| `dummy-tracker-simulator.php` | Simple realistic traffic | Quick development testing, demo data |
| `stress-test-simulator.php` | Advanced testing scenarios | Stress testing, security validation, edge cases |

**Use `dummy-tracker-simulator.php` when you need:**
- Quick, simple dummy data for development
- Realistic visitor traffic patterns only
- Easy date-range based generation

**Use `stress-test-simulator.php` when you need:**
- High-volume stress testing (100K+ records)
- Invalid/edge case data testing
- Security attack payload validation
- Parallel request processing
- Resumable checkpoints for large jobs

## CLI Usage

```bash
php bin/stress-test-simulator.php [options]
```

### Options

| Option | Description | Default |
|--------|-------------|---------|
| `--target=N` | Number of records (supports K/M suffix: 100K, 1M) | 1000 |
| `--workers=N` | Parallel HTTP workers | 10 |
| `--scenario=NAME` | Preset scenario: normal, stress, invalid, security, mixed | normal |
| `--invalid-ratio=N` | Ratio of invalid data (0.0-1.0) | 0.0 |
| `--attack-ratio=N` | Ratio of attack payloads (0.0-1.0) | 0.0 |
| `--days=N` | Days in date range | 30 |
| `--from=DATE` | Start date (YYYY-MM-DD) | -30 days |
| `--to=DATE` | End date (YYYY-MM-DD) | today |
| `--logged-in-ratio=N` | Ratio of logged-in visitors | 0.12 |
| `--resume` | Resume from checkpoint | false |
| `--checkpoint-id=ID` | Checkpoint identifier | auto |
| `--no-checkpoints` | Disable checkpoint saving | false |
| `--url=URL` | Target URL | auto-detect |
| `--dry-run` | Show config without running | false |
| `--verbose` | Verbose output | false |
| `--help` | Show help message | - |

### Data Generation Types

Control what types of data the simulator generates using ratio options:

#### `--invalid-ratio=N` (0.0 to 1.0)
Percentage of requests containing invalid/edge case data:
- Boundary values (negative numbers, MAX_INT, zero dimensions)
- Malformed strings (empty, oversized, null bytes)
- Missing required fields
- Type mismatches (string where int expected)
- Encoding issues (invalid UTF-8, control characters)

```bash
# 10% invalid data
php bin/stress-test-simulator.php --target=1000 --invalid-ratio=0.1
```

#### `--attack-ratio=N` (0.0 to 1.0)
Percentage of requests containing security attack payloads:
- SQL injection (UNION SELECT, blind boolean, time-based)
- XSS (script tags, event handlers, JS URIs)
- Command injection (shell commands, filter bypass)
- Path traversal (../../../wp-config.php)
- SSRF (internal services, metadata endpoints)
- Header injection (CRLF, host manipulation)

```bash
# 5% attack payloads
php bin/stress-test-simulator.php --target=1000 --attack-ratio=0.05
```

#### `--logged-in-ratio=N` (0.0 to 1.0)
Percentage of visitors simulated as logged-in WordPress users:
- Logged-in users have `user_id` set (vs null for guests)
- Different behavior patterns (more pages/session, lower bounce rate)
- Uses existing WordPress users or creates test users

```bash
# 20% logged-in visitors
php bin/stress-test-simulator.php --target=1000 --logged-in-ratio=0.2
```

### Scenarios (Presets)

Preset configurations that set optimal ratios for specific testing goals:

| Scenario | Invalid Ratio | Attack Ratio | Logged-in Ratio | Workers | Description |
|----------|---------------|--------------|-----------------|---------|-------------|
| `normal` | 0% | 0% | 12% | 10 | Standard realistic traffic |
| `stress` | 0% | 0% | 12% | 20+ | High-volume testing (100K+ records) |
| `invalid` | 50% | 0% | 12% | 10 | Focus on edge cases and malformed data |
| `security` | 0% | 20% | 12% | 10 | Attack payload validation |
| `mixed` | 10% | 5% | 15% | 15 | Combination of all data types |

```bash
# Use a preset scenario
php bin/stress-test-simulator.php --target=5000 --scenario=security

# Override specific ratios in a scenario
php bin/stress-test-simulator.php --target=5000 --scenario=mixed --attack-ratio=0.1
```

### Examples

```bash
# Stress test with 100K records
php bin/stress-test-simulator.php --target=100K --scenario=stress --workers=20

# Security testing
php bin/stress-test-simulator.php --target=5000 --scenario=security

# Custom mix of invalid and attack data
php bin/stress-test-simulator.php --target=10K --invalid-ratio=0.1 --attack-ratio=0.05

# Specify date range
php bin/stress-test-simulator.php --target=5000 --from=2024-01-01 --to=2024-03-31

# Resume interrupted simulation
php bin/stress-test-simulator.php --resume --checkpoint-id=my-test

# Dry run to see configuration
php bin/stress-test-simulator.php --target=1M --scenario=stress --dry-run
```

## PHPUnit Integration

Run the simulator tests:

```bash
# Run all simulator tests
composer test -- --group=simulator

# Run specific test groups
composer test -- --group=generators
composer test -- --group=security
composer test -- --group=stress
```

### Using in Custom Tests

```php
use WP_Statistics\Testing\Simulator\SimulatorConfig;
use WP_Statistics\Testing\Simulator\SimulatorRunner;

class MyTest extends WP_UnitTestCase
{
    public function test_system_handles_high_load(): void
    {
        $config = SimulatorConfig::forPHPUnit(admin_url('admin-ajax.php'));
        $config->targetRecords = 100;

        $runner = new SimulatorRunner($config);
        $results = $runner->run();

        $this->assertGreaterThan(90, $results['success']);
    }
}
```

## Architecture

### Directory Structure

```
wp-statistics/
├── bin/
│   ├── stress-test-simulator.php     # CLI entry point
│   ├── dummy-tracker-simulator.php   # Original simulator (reference)
│   ├── STRESS_TEST_SIMULATOR.md      # This documentation
│   ├── data/                         # Shared data files
│   │   ├── realistic-patterns.json   # Traffic patterns
│   │   ├── invalid-data.json         # Edge cases
│   │   ├── attack-payloads.json      # Security payloads
│   │   ├── countries.json            # Country data
│   │   ├── devices.json              # Device data
│   │   └── ...                       # Other JSON data files
│   ├── simulator/                    # Simulator classes
│   │   ├── SimulatorConfig.php       # Configuration
│   │   ├── SimulatorRunner.php       # Orchestrator
│   │   ├── SettingsConfigurator.php  # WP Statistics settings
│   │   ├── ResourceProvisioner.php   # WordPress content
│   │   ├── CheckpointManager.php     # Resumability
│   │   ├── Generators/
│   │   │   ├── AbstractDataGenerator.php
│   │   │   ├── RealisticVisitorGenerator.php
│   │   │   ├── InvalidDataGenerator.php
│   │   │   └── AttackPayloadGenerator.php
│   │   └── Http/
│   │       └── CurlMultiSender.php   # Parallel HTTP
│   └── checkpoints/                  # Checkpoint files (auto-created)
└── tests/integration/Simulator/
    ├── SimulatorTestCase.php
    ├── Test_RealisticVisitorGenerator.php
    ├── Test_InvalidDataGenerator.php
    ├── Test_AttackPayloadGenerator.php
    └── Test_SimulatorRunner.php
```

### Components

#### SimulatorRunner
Main orchestrator that coordinates:
1. Settings configuration (enables `bypass_ad_blockers`, `use_cache_plugin`)
2. Resource provisioning (creates posts/pages/users if needed)
3. Data generation (realistic, invalid, attack)
4. Parallel HTTP sending
5. Progress tracking and checkpointing

#### Data Generators

| Generator | Purpose |
|-----------|---------|
| `RealisticVisitorGenerator` | Realistic visitor profiles with device/browser correlation, geo data, session behavior |
| `InvalidDataGenerator` | Edge cases: boundary values, malformed strings, missing fields, type errors |
| `AttackPayloadGenerator` | Security payloads: SQL injection, XSS, path traversal, SSRF, etc. |

#### CurlMultiSender
High-performance HTTP sender with:
- Configurable concurrency (1-100 workers)
- Streaming results (memory efficient)
- Automatic retry on transient failures
- Response time tracking

#### CheckpointManager
Enables resumable simulations:
- Saves state every 10,000 records
- Tracks progress, statistics, elapsed time
- Validates config compatibility on resume

## Data Patterns

### Realistic Traffic

- **Logged-in vs Guest**: Configurable ratio (default 12% logged-in)
- **Device distribution**: Desktop 55%, Mobile 40%, Tablet 5%
- **Browser correlation**: iOS→Safari (85%), Android→Chrome (75%), Windows→Chrome (60%)
- **Temporal patterns**: Hourly distribution (peak 9-17), weekly (Mon-Thu higher), seasonal
- **Session behavior**: Logged-in users have more pages/session, lower bounce rate

### Invalid Data Categories

| Category | Examples |
|----------|----------|
| Boundary | -1, 0, MAX_INT, negative dimensions |
| Malformed | Empty strings, path traversal, invalid encodings |
| Missing | Required fields omitted |
| Overflow | 10KB+ strings |
| Encoding | NULL bytes, control characters, RTL override |
| Type | String where int expected, arrays, booleans |

### Attack Payload Categories

| Category | Severity | Examples |
|----------|----------|----------|
| SQL Injection | Critical | UNION SELECT, blind boolean, time-based |
| Command Injection | Critical | Shell commands, filter bypass |
| XXE | Critical | XML external entities |
| Deserialization | Critical | PHP object injection |
| XSS | High | Script tags, event handlers, JS URIs |
| Path Traversal | High | `../../../wp-config.php` |
| SSRF | High | Internal services, metadata endpoints |
| Header Injection | Medium | CRLF, host header |
| Encoding Bypass | Medium | Double URL encoding, unicode |

## Prerequisites

The simulator automatically configures required WP Statistics settings:

```php
// These settings are auto-enabled:
'bypass_ad_blockers' => '1'  // Use admin-ajax.php endpoint
'use_cache_plugin'   => '1'  // Enable client-side tracking
```

If no WordPress content exists, the simulator creates sample posts and pages.

## Checkpoints

Checkpoints enable resuming interrupted simulations:

```bash
# Start a named simulation
php bin/stress-test-simulator.php --target=1M --checkpoint-id=my-big-test

# If interrupted (Ctrl+C), resume later:
php bin/stress-test-simulator.php --resume --checkpoint-id=my-big-test

# Disable checkpoints for quick tests
php bin/stress-test-simulator.php --target=1000 --no-checkpoints
```

Checkpoint files are stored in `wp-content/plugins/wp-statistics/bin/checkpoints/`.

## Performance Tips

1. **Increase workers** for faster throughput: `--workers=50`
2. **Use SSD storage** for checkpoint writes
3. **Disable checkpoints** for small tests: `--no-checkpoints`
4. **Monitor memory** with large volumes
5. **Use scenarios** for optimized presets

## Troubleshooting

### "No resources available"
The simulator needs WordPress posts/pages. It auto-creates them, but if that fails:
```bash
wp post create --post_type=post --post_title="Test Post" --post_status=publish
```

### "Target URL not found"
Ensure WordPress is accessible and `admin-ajax.php` works:
```bash
curl -X POST "http://your-site/wp-admin/admin-ajax.php" -d "action=test"
```

### High failure rate
Check:
- WP Statistics is activated
- Settings are configured (simulator auto-configures)
- WordPress is not in maintenance mode

### Memory issues with large volumes
Use checkpoints and run in batches, or increase PHP memory limit.