# WP Statistics v15 — Feature Migration Status

This document tracks the migration status of all features from v14 to v15.

**Legend:**
- ✅ **Implemented** — Feature is fully working in v15
- 🚧 **Pending** — Route/menu exists, React page not yet built
- 🔜 **Planned** — Not yet started, will be implemented
- ❌ **Not Implemented** — Intentionally removed from v15 (dead code, deprecated, or moved to premium plugin)

---

## Core Systems

| Feature | v14 | v15 | Notes |
| --- | --- | --- | --- |
| Plugin Bootstrap | ✅ Singleton pattern | ✅ Service container | `CoreServiceProvider`, `AdminServiceProvider` |
| Autoloading | ✅ PSR-4 | ✅ PSR-4 | Same, `src/` namespace |
| Database Schema | ✅ Custom tables | ✅ Custom tables + migration system | `DatabaseManager`, versioned migrations |
| Option Storage | ✅ `wp_statistics` main option | ✅ Same + group options | `Option::getValue()`, `Option::getGroup()` |
| Cron / Scheduled Tasks | ✅ Legacy `Schedule` class | ✅ `CronManager` + event classes | Lazy-loaded, admin-visible |
| Background Processing | ✅ Direct execution | ✅ `BackgroundProcessFactory` | Async task queue |
| REST API | ✅ Add-on based | ✅ Core endpoints | `ToolsEndpoints`, `SettingsEndpoints` |
| Logging | ✅ Multiple providers | ✅ `FileProvider` only | `LoggerFactory` simplified |

## Admin UI

| Feature | v14 | v15 | Notes |
| --- | --- | --- | --- |
| Dashboard / Overview | ✅ PHP pages + Chart.js | ✅ React SPA | `ReactAppManager`, TanStack Router |
| Settings Page | ✅ PHP forms | ✅ React SPA | Hash routes `#/settings/*`, `SettingsRegistry` |
| Tools Page | ✅ PHP pages | ✅ React SPA | `#/tools/system-info`, `#/tools/scheduled-tasks` |
| Admin Menu | ✅ `AdminMenuManager` | ✅ Same, simplified | React hash routes for all pages |
| Admin Bar Stats | ✅ Raw CSS/JS from `resources/` | ✅ Vite-built from `resources/entries/` | `AdminBarManager` |
| Dashboard Widget | ✅ Raw CSS from `resources/` | ✅ Vite-built from `resources/entries/` | `DashboardWidgetManager` |
| Command Palette | ❌ N/A | ✅ Implemented | WordPress Cmd+K integration |
| Admin Notices | ✅ PHP notices | ✅ `NoticeManager` (React + legacy) | `DiagnosticNotice` registered |

## Analytics & Tracking

| Feature | v14 | v15 | Notes |
| --- | --- | --- | --- |
| Frontend Tracker | ✅ JS tracker | ✅ Same, Vite-built | `resources/frontend/` → `public/entries/frontend/` |
| Visitor Recording | ✅ Direct DB inserts | ✅ Same | `VisitorRecorder` |
| Views / Hits Recording | ✅ Direct DB inserts | ✅ Same | `ViewRecorder` |
| Online Users | ✅ Real-time tracking | ✅ Implemented | `useronline` table |
| Device Detection | ✅ Matomo Device Detector | ✅ Same | `DeviceHelper`, `UserAgent` |
| GeoIP / Geolocation | ✅ MaxMind GeoLite2 | ✅ MaxMind + DB-IP + Cloudflare | `GeolocationFactory`, multiple providers |
| Search Engine Detection | ✅ Built-in | ✅ Implemented | `SearchEngineDetector` |
| Referral Tracking | ✅ Built-in | ✅ Implemented | `ReferralsDatabase` |
| Daily Summary | ✅ Cron-based | ✅ Background process | `DailySummaryEvent` |
| Exclusions (IP, Role, URL) | ✅ Option-based | ✅ Same | Settings-driven |
| Cache Compatibility | ✅ REST-based tracker | ✅ Same | `bypass_ad_blockers` option |
| Anonymous Tracking | ✅ Hash-based | ✅ Same | Salt rotation, IP hashing |

## Content Analytics

| Feature | v14 | v15 | Notes |
| --- | --- | --- | --- |
| Analytics Query System | ✅ Multiple query classes | ✅ Unified `AnalyticsQueryHandler` | Single entry point for all queries |
| Pages / Posts Analytics | ✅ Per-page views | ✅ Implemented | Query-based |
| Author Analytics | ✅ Per-author stats | ✅ Implemented | Query-based |
| Taxonomy Analytics | ✅ Category/tag stats | ✅ Implemented | Query-based |
| Search Analytics | ✅ Search term tracking | ✅ Implemented | Query-based |
| Visitor Details | ✅ Individual visitor data | ✅ Implemented | Query-based |
| Geographic Analytics | ✅ Country/city data | ✅ Implemented | Query-based |
| Device / Browser / OS | ✅ Device breakdown | ✅ Implemented | Query-based |
| Referral Analytics | ✅ Referrer breakdown | ✅ Implemented | Query-based |

## Email Reports

| Feature | v14 | v15 | Notes |
| --- | --- | --- | --- |
| Email Report System | ✅ `Schedule::send_report()` | ✅ `EmailReportManager` + `EmailReportEvent` | Cron-managed |
| Report Templates | ✅ PHP template | ✅ Same, updated layout | `layout.php` |
| Report Scheduling | ✅ Daily/weekly/monthly | ✅ Same | `CronManager` |
| SMS Reports | ✅ WP SMS integration | ✅ `SmsProvider` | `MessagingFactory` |
| Email Logging | ✅ Option-based | ✅ WordPress `get_option` | `EmailReportLogger` |

## Privacy & Compliance

| Feature | v14 | v15 | Notes |
| --- | --- | --- | --- |
| Privacy Policy Generator | ✅ Built-in | ✅ Implemented | `PrivacyPolicyGenerator`, option-based checks |
| Consent Integration | ✅ WP Consent Level API | ✅ Implemented | Consent-driven: `statistics` → full, `statistics-anonymous` → anonymous |
| Data Export/Erasure | ✅ WordPress privacy tools | ✅ Implemented | WordPress hooks |
| IP Anonymization | ✅ Hash-based | ✅ Same | `store_ip` option |
| Privacy Audit | ✅ Full audit system (20+ classes) | 🚧 Pending React | Moved to Tools tab `#/tools/privacy-audit`, placeholder page created |

## Gutenberg & Content Integration

| Feature | v14 | v15 | Notes |
| --- | --- | --- | --- |
| Gutenberg Blocks | ✅ Webpack-built | ✅ Vite-built | `vite.config.blocks.js` |
| Shortcodes | ✅ `ShortcodeService` | ✅ Implemented (simplified) | Removed dead `ShortcodeManager` and `ShortcakeRegistrar` |
| Editor Metabox | ✅ Legacy metabox | ✅ Implemented | `EditorMetabox` — renders views/visitors via `AnalyticsQueryHandler` |
| Post Hit Column | ✅ Admin column | ✅ Implemented | `StatsColumnManager` — Views column on posts/pages/CPTs + users |

## Site Health

| Feature | v14 | v15 | Notes |
| --- | --- | --- | --- |
| Site Health Info | ✅ Debug information | ✅ Implemented | `SiteHealthInfo` (plugin settings only) |
| Site Health Tests | ✅ Status tests | ✅ Implemented | `SiteHealthTests` |
| Add-on Settings in Health | ✅ Per-addon debug info | ❌ Not Implemented | `getAddOnsSettings()` returns empty, moved to premium |

## Network / Multisite

| Feature | v14 | v15 | Notes |
| --- | --- | --- | --- |
| Network Admin Menu | ✅ Network settings | ✅ Implemented | `NetworkMenuManager` |
| Network Overview | ✅ Multisite stats | ✅ Implemented | Network admin page |

## Import / Export

| Feature | v14 | v15 | Notes |
| --- | --- | --- | --- |
| Settings Export | ✅ JSON export | ✅ Implemented | `ImportExportManager` |
| Settings Import | ✅ JSON import | ✅ Implemented | `ImportExportManager` |
| Data Backup | ✅ CSV/TSV export | ✅ Implemented | Multiple formats |

## Telemetry

| Feature | v14 | v15 | Notes |
| --- | --- | --- | --- |
| Anonymous Usage Data | ✅ Opt-in telemetry | ✅ Implemented | `AnonymizedUsageDataManager` |
| Marketing Campaigns | ✅ Remote fetch | ✅ Implemented | `MarketingCampaignFactory` |

## Premium Integration

| Feature | v14 | v15 | Notes |
| --- | --- | --- | --- |
| Premium Check | ✅ `LicenseHelper` | ✅ `is_plugin_active()` | Direct check, TODO for dedicated helper |
| Add-on Compatibility | ✅ Per-addon license system | ✅ Backward-compatible stubs | `Option::getAddonValue()` returns defaults |
| Content Registry | ❌ N/A | ✅ `useContentRegistry` | Premium injects React content via registry |
| Premium Modules | ✅ Individual add-on plugins | ✅ Single premium plugin | `BaseModule` pattern in premium |
| Premium Page | ✅ Add-ons / License Manager | 🚧 Pending React | Route `#/premium` registered, page TBD |

## Build System

| Feature | v14 | v15 | Notes |
| --- | --- | --- | --- |
| Legacy Build | ✅ `vite.config.legacy.js` | ❌ Not Implemented | Replaced by `vite.config.entries.js` |
| Entries Build | ❌ N/A | ✅ `vite.config.entries.js` | Admin bar, dashboard widget, tracker |
| Blocks Build | ✅ `vite.config.blocks.js` | ✅ Same | Gutenberg blocks |
| React Build | ❌ N/A | ✅ `vite.config.react.ts` | React SPA dashboard |

## Features Not Implemented in v15

These were intentionally removed as dead code, deprecated features, or functionality moved to the premium plugin.

| Feature | v14 | v15 | Reason |
| --- | --- | --- | --- |
| Add-on System | ✅ `Addons` component, per-addon options | ❌ Not Implemented | Replaced by single premium plugin model |
| License Manager | ✅ License API, migration, addon management | ❌ Not Implemented | Replaced by premium plugin activation |
| Notification System | ✅ Remote notifications, bell icon, AJAX | ❌ Not Implemented | `NotificationFactory`, cron event deleted |
| Help Center | ✅ Help page with diagnostics | 🚧 Pending React | Route `#/help` registered, page TBD |
| Legacy Admin JS/CSS | ✅ admin.min.js, Chart.js vendor, jqvmap | ❌ Not Implemented | `resources/legacy/` (156 files, 6.6 MB) deleted |
| Post Summary Provider | ✅ Post stats in editor | ❌ Not Implemented | `PostSummaryDataProvider` deleted |
| Tracker Provider | ✅ Alternative log provider | ❌ Not Implemented | Always uses `FileProvider` now |
| Event/Parameter Decorators | ✅ Decorator classes | ❌ Not Implemented | Zero references, dead code |
| AjaxOptionUpdater | ✅ Legacy AJAX option handler | ❌ Not Implemented | Dead code |
| ModalHandler | ✅ Admin modal system | ❌ Not Implemented | Dead code |
| Shortcake UI | ✅ Shortcake plugin support | ❌ Not Implemented | Plugin abandoned since 2019 |
| Addon Promo Component | ✅ React addon promotion card | ❌ Not Implemented | Inline premium promo used instead |
