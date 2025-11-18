# WP Statistics Build & Development Guide

This documentation covers the assets build system and development workflow for the WP Statistics plugin.

## Table of Contents

1. [Overview](#overview)
2. [Directory Structure](#directory-structure)
3. [Build System](#build-system)
4. [Development Workflow](#development-workflow)
5. [Asset Migration](#asset-migration)

---

## Overview

The WP Statistics plugin uses a modern build system with clear separation between source files and built assets:

- **Source files**: `resources/` (development only, excluded from distribution)
- **Built assets**: `public/` (production builds, included in distribution)
- **Legacy assets**: `assets/` (kept for backward compatibility with add-ons)
- **Build tools**: Vite 7.x for legacy/React builds

### Key Features

- Modern build tooling (Vite 7.x with Rolldown)
- Faster builds with optimized bundling
- Clear source vs. build separation
- Automatic vendor file copying
- Proper ES module handling
- All assets properly namespaced
- Backward compatibility layer

---

## Directory Structure

```
wp-statistics/
├── resources/              # Source files (NOT in distribution)
│   ├── legacy/
│   │   ├── entries/       # Build entry points
│   │   │   ├── admin.js
│   │   │   ├── background-process.js
│   │   │   ├── tinymce.js
│   │   │   └── mini-chart.js
│   │   ├── javascript/    # Legacy JS modules
│   │   │   ├── plugin/    # Third-party plugins
│   │   │   ├── components/
│   │   │   ├── filters/
│   │   │   ├── meta-box/
│   │   │   ├── pages/
│   │   │   └── Tinymce/
│   │   ├── sass/          # SCSS source files
│   │   │   ├── admin.scss
│   │   │   ├── rtl.scss
│   │   │   ├── frontend.scss
│   │   │   └── mail.scss
│   │   ├── vendor/        # Vendor libraries (source)
│   │   │   ├── chartjs/
│   │   │   ├── datepicker/
│   │   │   ├── jqvmap/
│   │   │   └── select2/
│   │   └── mail/          # Email template sources
│   │
│   ├── frontend/          # Frontend source
│   │   ├── entries/
│   │   │   └── tracker.js
│   │   └── js/
│   │       ├── user-tracker.js
│   │       ├── event-tracker.js
│   │       └── tracker.js
│   │
│   ├── react/             # React app source
│   │   ├── main.tsx       # React entry point
│   │   ├── app.tsx        # Main App component
│   │   ├── components/    # React components
│   │   ├── routes/        # Tanstack Router routes
│   │   ├── services/      # API services
│   │   └── images/        # React image assets
│   │
│   └── json/              # JSON data files
│       └── source-channels.json
│
├── public/                # Built assets (IN distribution)
│   ├── legacy/
│   │   ├── js/            # Built JavaScript
│   │   │   ├── admin.min.js         # ~258 KB
│   │   │   ├── background-process.min.js
│   │   │   ├── tinymce.min.js
│   │   │   ├── chartjs/             # Chart.js library
│   │   │   │   ├── chart.umd.min.js
│   │   │   │   └── chart-matrix.min.js
│   │   │   ├── datepicker/          # Date picker libraries
│   │   │   ├── jqvmap/              # Vector map libraries
│   │   │   └── select2/             # Select2 library
│   │   │
│   │   └── css/           # Built CSS
│   │       ├── admin.min.css        # ~1 MB
│   │       ├── rtl.min.css          # ~40 KB
│   │       ├── frontend.min.css     # ~13 KB
│   │       ├── mail.min.css         # ~3 KB
│   │       ├── datepicker/
│   │       ├── jqvmap/
│   │       └── select2/
│   │
│   ├── frontend/          # Frontend assets
│   │   ├── js/
│   │   │   ├── tracker.min.js       # ~9 KB
│   │   │   ├── tracker.js
│   │   │   ├── mini-chart.min.js
│   │   │   └── chartjs/
│   │   │       └── chart.umd.min.js
│   │   └── css/
│   │       └── frontend.min.css
│   │
│   ├── react/             # React app build
│   │   ├── assets/        # Built JS/CSS chunks
│   │   ├── images/        # React images (auto-copied)
│   │   └── .vite/
│   │       └── manifest.json
│   │
│   ├── images/            # Plugin images
│   │   ├── flags/         # Country flags
│   │   ├── mail/          # Email template images
│   │   ├── browser/       # Browser icons
│   │   ├── device/        # Device icons
│   │   └── locked/        # Premium feature images
│   │
│   └── json/              # JSON data
│       └── source-channels.min.json
│
└── assets/                # Legacy assets (backward compatibility)
    ├── css/               # Old CSS files (for add-ons)
    ├── js/                # Old JS files (for add-ons)
    ├── images/            # Shared images
    ├── json/              # Old JSON files
    └── mail/              # Old email templates
```

---

## Build System

### Build Commands

```bash
# Build everything (legacy + react)
pnpm run build

# Build individual parts
pnpm run build:legacy   # Legacy JS/CSS (Vite)
pnpm run build:react    # React app (Vite)

# Development modes
pnpm run dev            # React dev server (HMR)
pnpm run dev:react      # Same as above
pnpm run dev:legacy     # Legacy watch mode

# Watch modes
pnpm run watch          # Watch legacy assets
pnpm run start          # Alias for dev
```

### Build Configurations

#### 1. Legacy Assets (`vite.config.legacy.js`)

**Entry Points:**
- `resources/legacy/entries/admin.js` → `public/legacy/js/admin.min.js`
- `resources/legacy/entries/background-process.js` → `public/legacy/js/background-process.min.js`
- `resources/legacy/entries/tinymce.js` → `public/legacy/js/tinymce.min.js`
- `resources/frontend/entries/tracker.js` → `public/frontend/js/tracker.min.js`
- `resources/legacy/entries/mini-chart.js` → `public/frontend/js/mini-chart.min.js`
- `resources/legacy/sass/admin.scss` → `public/legacy/css/admin.min.css`
- `resources/legacy/sass/rtl.scss` → `public/legacy/css/rtl.min.css`
- `resources/legacy/sass/frontend.scss` → `public/legacy/css/frontend.min.css`
- `resources/legacy/sass/mail.scss` → `public/legacy/css/mail.min.css`

**Custom Plugins:**
- `cleanOutputDir()` - Cleans public/legacy before build
- `jQueryReadyWrapper()` - Wraps admin.min.js in jQuery ready
- `inlineAdminSources()` - Bundles all admin JS modules
- `inlineBackgroundProcess()` - Bundles background process
- `inlineTinyMCE()` - Bundles TinyMCE scripts
- `inlineTrackerScripts()` - Bundles tracker scripts
- `copyVendorFiles()` - Copies vendor libraries from resources/legacy/vendor
- `copyJsonAssets()` - Minifies and copies JSON files
- `moveFrontendAssets()` - Moves frontend files to public/frontend

**Key Features:**
- Terser minification
- LightningCSS for CSS minification
- Modern SCSS compiler API
- Automatic vendor file copying
- jQuery externalized (WordPress provides it)
- Chart.js externalized (loaded separately)
- Proper asset file naming

#### 2. React App (`vite.config.react.ts`)

**Entry Point:**
- `resources/react/main.tsx` → `public/react/assets/main-[hash].js`

**Custom Plugins:**
- `copyImages()` - Copies images from resources/react/images to public/react/images
- `@tanstack/router-plugin` - Auto-generates routes
- `@tailwindcss/vite` - TailwindCSS v4 native integration

**Key Features:**
- TypeScript support
- Tanstack Router with auto code-splitting
- TailwindCSS v4
- Vite manifest.json for WordPress integration
- ES modules for modern browsers

---

## Development Workflow

### Starting Development

```bash
# 1. Install dependencies
pnpm install

# 2. Start React dev server (HMR enabled)
pnpm run dev

# 3. In another terminal, watch legacy assets (optional)
pnpm run dev:legacy
```

### Adding New Assets

#### Adding a new JS module (Legacy Admin)
1. Create file in `resources/legacy/javascript/`
2. Add import path to virtual bundle in `vite.config.legacy.js`
3. Build: `pnpm run build:legacy`

#### Adding a new SCSS file
1. Create file in `resources/legacy/sass/`
2. Import in main SCSS file or add as new entry in `vite.config.legacy.js`
3. Build: `pnpm run build:legacy`

#### Adding vendor libraries
1. Place in `resources/legacy/vendor/[library-name]/`
2. Files automatically copied to `public/legacy/js/` or `public/legacy/css/`
3. Update PHP code to reference from `public/legacy/`

#### Adding JSON data files
1. Place in `resources/json/`
2. Files automatically minified and copied to `public/json/`
3. Update PHP code to reference `public/json/[filename].min.json`

#### Adding React images
1. Place in `resources/react/images/`
2. Images automatically copied to `public/react/images/` during build
3. Reference in templates as `WP_STATISTICS_URL . 'public/react/images/[image].jpg'`

### Creating Production Builds

```bash
# Build everything
pnpm run build

# This runs:
# 1. pnpm run build:legacy  (Vite - Legacy assets)
# 2. pnpm run build:react   (Vite - React app)
```

**Build Output:**
- Legacy JS: ~260 KB (admin + background + tinymce)
- Legacy CSS: ~1.1 MB (admin + rtl + frontend + mail)
- Frontend JS: ~10 KB (tracker + mini-chart)
- Vendor files: ~200 KB (Chart.js + others)
- React app: Variable (depends on components)

### Distribution

**Excluded from distribution (`.distignore`):**
```
/resources              # Source files
/docs                   # Build documentation
/node_modules
vite.config.legacy.js
vite.config.react.ts
tsconfig.*.json
eslint.config.js
```

**Included in distribution:**
```
/public/legacy/         # Built legacy assets
/public/frontend/       # Built frontend assets
/public/react/          # Built React app
/public/images/         # Plugin images
/public/json/           # JSON data files
/assets/                # Legacy assets (backward compatibility)
```

---

## Asset Migration

### From Old Structure to New Structure

The plugin has migrated from the old `assets/` structure to a modern `public/` and `resources/` structure:

**Old Structure (Deprecated):**
```
assets/
├── dev/              # Source files
├── css/              # Built CSS
├── js/               # Built JS
└── images/           # Images
```

**New Structure (Current):**
```
resources/            # Source files
public/               # Built assets
assets/               # Kept for backward compatibility
```

### Backward Compatibility

The `assets/` folder is maintained for backward compatibility with add-ons that may reference old paths:

- **assets/images/** - Still available, contains all plugin images
- **assets/css/** - Old built CSS files (maintained for add-ons)
- **assets/js/** - Old built JS files (maintained for add-ons)
- **assets/json/** - Old JSON files (maintained for add-ons)

**New code should reference:**
- `public/legacy/` for admin assets
- `public/frontend/` for frontend assets
- `public/images/` for images
- `public/json/` for data files

---

## Troubleshooting

### Build fails with "Cannot find module"

**Solution:** Run `pnpm install` to ensure all dependencies are installed.

### React app not rendering

**Check:**
1. Manifest exists: `public/react/.vite/manifest.json`
2. Main file key is `'main.tsx'` not `'src/main.tsx'`
3. ReactHandler.php uses correct manifest key

### Assets loading from wrong path

**Check:**
1. PHP code uses correct asset directory (`public/legacy/` or `public/frontend/`)
2. Assets::script() has correct `$isInPublic` parameter
3. BaseAssets class has correct `$assetDir` property

### Chart.js errors in console

**Check:**
1. chart.umd.min.js loads separately before admin.min.js
2. chart.umd.min.js is externalized, not bundled
3. WordPress dependency chain: jQuery → Chart → Admin

### Vendor files not copied

**Check:**
1. Files exist in `resources/legacy/vendor/[library]/`
2. `copyVendorFiles()` plugin is enabled in vite.config.legacy.js
3. Build ran successfully without errors

### Mini-chart 404 error

**Check:**
1. File exists at `public/frontend/js/mini-chart.min.js`
2. PHP uses `Assets::script('mini-chart', 'js/mini-chart.min.js', [], [], true, false, null, '', '', true)`
3. Last parameter (`$isInPublic = true`) is set correctly

---

## Additional Resources

- [Vite Documentation](https://vitejs.dev/)
- [Tanstack Router](https://tanstack.com/router)
- [TailwindCSS v4](https://tailwindcss.com/)
- [WordPress Asset Management](https://developer.wordpress.org/themes/basics/including-css-javascript/)

---

**Last Updated:** November 2024
**Build System:** Vite 7.x (Rolldown)
**Package Manager:** pnpm