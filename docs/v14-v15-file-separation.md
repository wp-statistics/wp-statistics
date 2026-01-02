# WP Statistics v14 vs v15 File Separation

This document describes the separation between v14 (legacy) and v15 (new) architectures in WP Statistics.

## Architecture Overview

### v15 (New Architecture)

**Location:** `/src/` and `/resources/`

- All namespaced PHP classes (`WP_Statistics\*`)
- Modern service-oriented architecture
- React-based frontend components
- TanStack Router for navigation
- **NO dependency on legacy `/includes/` files**

**Entry Point:** `/src/Bootstrap.php`

### v14 (Legacy Architecture)

**Location:** `/includes/` and `/views/`

- Non-namespaced classes (`class-wp-statistics-*.php`)
- Procedural/OOP hybrid
- PHP-based templates
- jQuery-based frontend

**Entry Point:** `/includes/class-wp-statistics.php`

---

## Conditional Loading

The plugin uses conditional loading based on migration status:

```php
// In wp-statistics.php (main plugin file)
require_once WP_STATISTICS_DIR . 'vendor/autoload.php';
\WP_Statistics\Bootstrap::init();

// In src/Bootstrap.php
$migrationComplete = Option::getOptionGroup('db', 'migrated', false);

if ($migrationComplete || true) { // TODO: Remove '|| true' when v15 is stable
    // v15 Architecture - Pure new code
    self::initV15();
} else {
    // v14 Legacy Architecture
    require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics.php';
    \WP_Statistics::instance();
}
```

### Key Principle

**v15 minimizes dependency on legacy `/includes/` files.**

All v15 functionality must be implemented in `/src/` using PSR-4 autoloading.

### Core Utilities (Shared)

Some utility classes from `/includes/` are still required in v15 (no UI, just helpers):

- `class-wp-statistics-helper.php` - Core utility functions
- `class-wp-statistics-db.php` - Database utilities
- `class-wp-statistics-option.php` - Option management
- `class-wp-statistics-timezone.php` - Timezone handling
- `class-wp-statistics-user.php` - User utilities
- `class-wp-statistics-country.php` - Country data
- `class-wp-statistics-ip.php` - IP utilities
- `class-wp-statistics-geoip.php` - GeoIP functions
- `class-wp-statistics-visitor.php` - Visitor data
- `class-wp-statistics-pages.php` - Page data
- `class-wp-statistics-meta-box.php` - Meta box utilities
- `class-wp-statistics-shortcode.php` - Shortcode support
- `class-wp-statistics-widget.php` - Widget support

These will eventually be migrated to `/src/` as pure PSR-4 classes.

### v15 Services Loaded

The following services from `/src/` are initialized in v15 mode:

**Global Services (admin + frontend):**
- `TrackerControllerFactory` - Tracking controller
- `CronEventManager` - Cron scheduling
- `CustomEventManager` - Custom events
- `HooksManager` - WordPress hooks
- `AdminBar` - Admin bar integration
- `ReferralsManager` - Referral tracking
- `AnonymizedUsageDataManager` - Usage data
- `NotificationManager` - Notifications
- `MarketingCampaignManager` - Marketing campaigns

**Admin Services:**
- `DashboardManager` - React dashboard
- `AdminManager` - Admin menu and pages
- `SettingsManager` - React settings page
- `ContentAnalyticsManager`, `AuthorAnalyticsManager`, `CategoryAnalyticsManager` - Analytics
- `PageInsightsManager`, `VisitorInsightsManager` - Insights
- `GeographicManager`, `DevicesManager` - Geographic and device data
- `OverviewManager` - Overview page
- `PrivacyAuditManager`, `HelpCenterManager` - Support features
- `IntegrationsManager`, `LicenseManagementManager` - Integrations and licensing
- `TrackerDebuggerManager` - Tracker debugging
- `MetaboxManager`, `ExclusionsManager`, `FilterManager` - Admin utilities
- `ResourceSynchronizer`, `AjaxBackgroundProcessManager` - Background tasks

### Migration Flag

- **Option:** `wp_statistics_db.migrated`
- **Type:** Boolean
- **Default:** `false`
- **Set by:** `MigrationHandler` after successful migration

---

## Directory Structure

```
wp-statistics/
├── wp-statistics.php            # Loads autoloader + Bootstrap::init()
│
├── src/                         # v15 New (PSR-4 autoloaded)
│   ├── Bootstrap.php            # v15 entry point, decides v14/v15
│   ├── Service/
│   │   ├── Admin/
│   │   │   ├── Settings/        # v15 Settings page
│   │   │   └── ...
│   │   └── ...
│   └── ...
│
├── resources/                   # Frontend assets
│   └── react/                   # React app
│       └── src/
│           ├── routes/
│           │   ├── settings/    # Settings routes
│           │   └── ...
│           └── components/
│               ├── settings/    # Settings components
│               └── ...
│
├── includes/                    # v14 Legacy (loaded only in v14 mode)
│   ├── class-wp-statistics.php  # v14 entry point
│   ├── class-wp-statistics-*.php
│   ├── admin/
│   └── ...
│
└── views/                       # PHP views (both v14 and v15)
    └── pages/
        ├── settings/            # v15 Settings mount point
        └── ...
```

---

## Migration Approach

### For Users

1. User sees "Migrate to v15" notice in admin
2. User clicks to start migration
3. `MigrationHandler` runs schema migrations
4. `wp_statistics_db.migrated` is set to `true`
5. Next page load uses v15 architecture
6. User sees new React-based interface

### For Developers

1. New features are added ONLY to v15 (`/src/`, `/resources/`)
2. Legacy code in `/includes/` is not modified (except critical bug fixes)
3. v14 code remains in plugin for backward compatibility
4. v15 must be completely independent of `/includes/`

---

## Notes

- The `|| true` in conditional loading is temporary for development
- Remove when v15 is stable and ready for production
- Fresh installs will automatically get v15 (migration flag defaults to false, but `|| true` bypasses)
- Existing users need to run migration to get v15
