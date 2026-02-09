# Compatibility And Legacy Surface (v15 Transition)

WP Statistics v15 introduces a new architecture under `src/` (AnalyticsQuery, normalized tables, React admin UI).
During the transition, WP Statistics also ships a backward-compatibility layer to prevent fatal errors in older add-ons
that still call the legacy APIs (v14-style classes, globals, and table usage).

This document defines what is considered "legacy/compat" vs "v15 core", and what is safe (or unsafe) to remove.

## Goals

- Keep older add-ons working without fatals while v15 is still under development.
- Make it obvious (for humans and AI agents) where NOT to implement new functionality.
- Provide a single source-of-truth for:
  - legacy PHP APIs that add-ons may call
  - legacy (v14) tables vs v15 replacements
  - add-on tables that must be preserved/managed

## Directory Zones

- `src/`: v15 codebase. Prefer adding/maintaining new functionality here.
- `includes/`: legacy compatibility layer (WP_STATISTICS namespace). Do not add new features here.
- `resources/legacy/` and `public/legacy/`: legacy admin assets still shipped for compatibility.
- `src/functions.php`: public function surface (includes deprecated wrappers for older add-on code).

## Backward-Compatibility PHP Surface

### Legacy classes (WP_STATISTICS namespace)

These are loaded by `src/Bootstrap.php` (`loadLegacyUtilities()`) and are intended for add-on compatibility.
Do not remove them until all supported add-ons migrate away.

- `WP_STATISTICS\\Option` (`includes/class-wp-statistics-option.php`)
- `WP_STATISTICS\\DB` (`includes/class-wp-statistics-db.php`)
- `WP_STATISTICS\\TimeZone` (`includes/class-wp-statistics-timezone.php`)
- `WP_STATISTICS\\User` (`includes/class-wp-statistics-user.php`)
- `WP_STATISTICS\\Helper` (`includes/class-wp-statistics-helper.php`)
- `WP_STATISTICS\\Country` (`includes/class-wp-statistics-country.php`)
- `WP_STATISTICS\\IP` (`includes/class-wp-statistics-ip.php`)
- `WP_STATISTICS\\Visitor` (`includes/class-wp-statistics-visitor.php`)
- `WP_STATISTICS\\Menus` (`includes/class-wp-statistics-menus.php`)
- `WP_STATISTICS\\Schedule` (`includes/class-wp-statistics-schedule.php`)
- `WP_STATISTICS\\Pages` (`includes/class-wp-statistics-pages.php`)
- `WP_STATISTICS\\Admin_Template` (`includes/admin/class-wp-statistics-admin-template.php`)
- `WP_STATISTICS\\Admin_Assets` (`includes/admin/class-wp-statistics-admin-assets.php`)

Policy:

- Keep signatures stable.
- If an API is deprecated, call `_deprecated_function()` (preferably once per request) and delegate to a v15 service
  or return a safe default.
- Avoid adding new behavior; instead implement new code in v15 services and keep legacy as a thin adapter.

### Global functions (`wp_statistics_*`)

See `src/functions.php` for the supported public API surface. v15 recommends `wp_statistics_query()` for data access.
Some older functions are deprecated and/or map legacy time ranges to v15 queries.

Policy:

- Keep function names callable for add-ons.
- Internals should use v15 services (AnalyticsQuery) where possible.
- Mark deprecated functions with `@deprecated` and `_deprecated_function()`.

### v15 model classes (WP_Statistics\\Models\\*)

Some add-ons still import `WP_Statistics\\Models\\{VisitorsModel, ViewsModel, EventsModel, PostsModel, OnlineModel}`.
These are effectively part of the current add-on surface until add-ons migrate to AnalyticsQuery.

Policy:

- Do not remove model classes that are still used by supported add-ons.
- Prefer migrating add-ons to AnalyticsQuery before deleting deprecated models.

## Database Tables

### Table naming

Both v14 and v15 tables follow the WordPress prefix plus `statistics_...`.
See `src/Service/Database/DatabaseSchema.php` for the authoritative registry.

### Legacy tables (v14 schema)

Defined in `DatabaseSchema::$legacyTables` as table keys:

- `useronline`
- `visitor`
- `pages`
- `historical`
- `visitor_relationships`

These are deprecated in v15. New code should not query these tables directly.

### v15 replacements (high-level mapping)

This is conceptual mapping used for migration and to guide new code:

- `visitor` (v14) -> `visitors` + `sessions` (v15 normalized visitors/sessions)
- `pages` (v14) -> `views` + `resources` + `resource_uris`
- `historical` (v14) -> `summary` + `summary_totals`
- `useronline` (v14) -> v15 real-time online visitors system (query-based)
- `visitor_relationships` (v14) -> v15 normalized relations via resources/parameters

## Add-on Tables

Defined in `DatabaseSchema::$addonTables` (table keys):

- `data-plus`: `events`
- `advanced-reporting`: `ar_outbox`
- `marketing`: `campaigns`, `goals`

Policy:

- If an add-on is supported, its tables must be included in uninstall/maintenance/migrations.
- Keep the registry updated when an add-on adds/removes tables.

## Removal Guidelines

- Safe to remove:
  - Code that is not referenced by the main plugin, tests, or supported add-ons (verify via ripgrep on `wp-statistics-*`).
- Not safe to remove:
  - Any `includes/` legacy class listed above while supported add-ons still reference it.
  - Any model/table/function referenced by supported add-ons.
- Recommended deprecation process:
  1. Add deprecation notices + delegate/return safe defaults.
  2. Observe/measure usage (optional hook/logging).
  3. Remove only after migration window.

