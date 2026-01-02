# WP Statistics v14 vs v15 File Separation

This document describes the separation between v14 (legacy) and v15 (new) architectures in WP Statistics.

## Architecture Overview

### v15 (New Architecture)

**Location:** `/src/` and `/resources/`

- All namespaced PHP classes (`WP_Statistics\*`)
- Modern service-oriented architecture
- React-based frontend components
- TanStack Router for navigation

**Entry Point:** `/src/Bootstrap.php`

### v14 (Legacy Architecture)

**Location:** `/includes/` and `/views/`

- Non-namespaced classes (`class-wp-statistics-*.php`)
- Procedural/OOP hybrid
- PHP-based templates
- jQuery-based frontend

**Entry Point:** `/includes/class-wp-statistics.php` → `includes()` method

---

## Conditional Loading

The plugin uses conditional loading based on migration status:

```php
// In includes/class-wp-statistics.php → plugin_setup()

$migrationComplete = Option::getOptionGroup('db', 'migrated', false);

if ($migrationComplete || true) { // TODO: Remove '|| true' when v15 is stable
    // v15 Architecture
    \WP_Statistics\Bootstrap::init();
} else {
    // v14 Legacy Architecture
    $this->includes();
}
```

### Migration Flag

- **Option:** `wp_statistics_db.migrated`
- **Type:** Boolean
- **Default:** `false`
- **Set by:** `MigrationHandler` after successful migration

---

## File Categories

### Legacy Utilities (Keep in /includes/, used by v15)

These files are utility classes with no UI that v15 still needs:

| File | Purpose |
|------|---------|
| `class-wp-statistics-helper.php` | Core utilities |
| `class-wp-statistics-db.php` | Database utilities |
| `class-wp-statistics-option.php` | Option management |
| `class-wp-statistics-timezone.php` | Timezone handling |
| `class-wp-statistics-user.php` | User utilities |
| `class-wp-statistics-country.php` | Country data |
| `class-wp-statistics-ip.php` | IP utilities |
| `class-wp-statistics-geoip.php` | GeoIP functions |
| `class-wp-statistics-user-agent.php` | User agent parsing |
| `class-wp-statistics-user-online.php` | Online tracking |
| `class-wp-statistics-pages.php` | Page analytics |
| `class-wp-statistics-visitor.php` | Visitor data |
| `class-wp-statistics-historical.php` | Historical data |
| `class-wp-statistics-referred.php` | Referrer tracking |
| `class-wp-statistics-search-engine.php` | Search engines |
| `class-wp-statistics-exclusion.php` | Exclusions |
| `class-wp-statistics-purge.php` | Data purging |
| `class-wp-statistics-shortcode.php` | Shortcodes |
| `class-wp-statistics-widget.php` | Widgets |
| `class-wp-statistics-privacy-*.php` | Privacy exports/erasers |

### Legacy Admin (v14 only, replaced in v15)

| Legacy File | v15 Replacement |
|-------------|-----------------|
| `class-wp-statistics-menus.php` | `AdminManager` |
| `class-wp-statistics-meta-box.php` | `MetaboxManager` |
| `class-wp-statistics-admin-bar.php` | `Service\Admin\AdminBar` |
| `/includes/admin/templates/settings/` | React Settings (TODO) |
| `/includes/admin/templates/metabox/` | `MetaboxManager` |

### v15 Services

Located in `/src/Service/`:

| Service | Purpose |
|---------|---------|
| `Admin/DashboardBootstrap/` | React dashboard |
| `Admin/AdminManager` | Menu registration |
| `Admin/AdminBar` | Admin bar |
| `Admin/Settings/` | React settings (TODO) |
| `Analytics/` | Analytics queries |
| `Database/` | Database operations |
| `EmailReport/` | Email reporting (TODO) |
| `Tracking/` | Visitor tracking |

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
3. Utility classes may be gradually migrated to `/src/` with aliases
4. v14 code remains in plugin for backward compatibility

---

## Directory Structure

```
wp-statistics/
├── includes/                    # v14 Legacy
│   ├── class-wp-statistics-*.php
│   ├── admin/
│   │   ├── pages/
│   │   ├── templates/
│   │   │   ├── emails/
│   │   │   ├── metabox/
│   │   │   └── settings/
│   │   └── TinyMCE/
│   ├── api/
│   └── libraries/
│
├── src/                         # v15 New (PSR-4 autoloaded)
│   ├── Bootstrap.php            # v15 entry point
│   ├── Service/
│   │   ├── Admin/
│   │   │   ├── AdminBar/
│   │   │   ├── AdminManager/
│   │   │   ├── DashboardBootstrap/
│   │   │   ├── Settings/         # TODO
│   │   │   └── ...
│   │   ├── EmailReport/          # TODO
│   │   ├── Database/
│   │   └── Tracking/
│   ├── Models/
│   └── ...
│
├── resources/                   # Frontend assets
│   └── react/                   # React app
│       └── src/
│           ├── routes/          # TanStack Router routes
│           │   ├── settings/    # TODO
│           │   └── ...
│           └── components/
│
└── views/                       # PHP views
    └── pages/
        ├── root/                # React mount point
        └── settings/            # TODO
```

---

## TODO Items

### Phase 0: v14/v15 Separation
- [x] Create `/src/Bootstrap.php`
- [x] Modify main bootstrap for conditional loading
- [x] Create this documentation

### Phase 1: v15 Settings
- [ ] Create `/src/Service/Admin/Settings/`
- [ ] Create React routes for settings
- [ ] Migrate settings fields to React

### Phase 2: Email Reporting
- [ ] Create `/src/Service/EmailReport/`
- [ ] Create email builder React component
- [ ] Implement email scheduling

---

## Notes

- The `|| true` in conditional loading is temporary for development
- Remove when v15 is stable and ready for production
- Fresh installs will automatically get v15 (migration flag defaults to false, but `|| true` bypasses)
- Existing users need to run migration to get v15
