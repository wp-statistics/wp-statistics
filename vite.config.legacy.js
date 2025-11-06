import { defineConfig } from 'vite';
import { resolve } from 'path';
import { readFileSync, writeFileSync, mkdirSync, rmSync, readdirSync, statSync, cpSync, existsSync } from 'fs';
import { join } from 'path';
import { globSync } from 'glob';

// Custom plugin to wrap JS in jQuery ready
function jQueryReadyWrapper() {
  return {
    name: 'jquery-ready-wrapper',
    generateBundle(options, bundle) {
      for (const fileName in bundle) {
        if (bundle[fileName].type === 'chunk' && fileName.endsWith('.js')) {
          const chunk = bundle[fileName];
          if (fileName.includes('admin.min.js') || fileName.includes('background-process.min.js')) {
            chunk.code = `jQuery(document).ready(function ($) {${chunk.code}});`;
          }
        }
      }
    }
  };
}

// Custom plugin to inline source files for admin bundle
function inlineAdminSources() {
  return {
    name: 'inline-admin-sources',
    resolveId(id) {
      if (id === 'virtual:admin-bundle') {
        return id;
      }
    },
    load(id) {
      if (id === 'virtual:admin-bundle') {
        const files = [
          './resources/legacy/javascript/plugin/*.js',
          './resources/legacy/javascript/config.js',
          './resources/legacy/javascript/ajax.js',
          './resources/legacy/javascript/placeholder.js',
          './resources/legacy/javascript/helper.js',
          './resources/legacy/javascript/chart.js',
          './resources/legacy/javascript/filters/*.js',
          './resources/legacy/javascript/components/*.js',
          './resources/legacy/javascript/meta-box.js',
          './resources/legacy/javascript/meta-box/*.js',
          './resources/legacy/javascript/pages/*.js',
          './resources/legacy/javascript/run.js',
          './resources/legacy/javascript/image-upload.js',
        ];

        const resolvedFiles = files.flatMap(pattern => globSync(pattern));
        const code = resolvedFiles.map(file => {
          try {
            return readFileSync(file, 'utf-8');
          } catch (e) {
            console.warn(`Could not read file: ${file}`);
            return '';
          }
        }).join('\n');

        return code;
      }
    }
  };
}

// Custom plugin for background process
function inlineBackgroundProcess() {
  return {
    name: 'inline-background-process',
    resolveId(id) {
      if (id === 'virtual:background-process') {
        return id;
      }
    },
    load(id) {
      if (id === 'virtual:background-process') {
        try {
          return readFileSync('./resources/legacy/javascript/background-process.js', 'utf-8');
        } catch (e) {
          console.warn('Could not read background-process.js');
          return '';
        }
      }
    }
  };
}

// Custom plugin for TinyMCE
function inlineTinyMCE() {
  return {
    name: 'inline-tinymce',
    resolveId(id) {
      if (id === 'virtual:tinymce') {
        return id;
      }
    },
    load(id) {
      if (id === 'virtual:tinymce') {
        const files = globSync('./resources/legacy/javascript/Tinymce/*.js');
        const code = files.map(file => {
          try {
            return readFileSync(file, 'utf-8');
          } catch (e) {
            console.warn(`Could not read file: ${file}`);
            return '';
          }
        }).join('\n');
        return code;
      }
    }
  };
}

// Custom plugin for tracker scripts
function inlineTrackerScripts() {
  return {
    name: 'inline-tracker',
    resolveId(id) {
      if (id === 'virtual:tracker') {
        return id;
      }
    },
    load(id) {
      if (id === 'virtual:tracker') {
        const files = [
          './resources/frontend/js/user-tracker.js',
          './resources/frontend/js/event-tracker.js',
          './resources/frontend/js/tracker.js',
        ];
        const code = files.map(file => {
          try {
            return readFileSync(file, 'utf-8');
          } catch (e) {
            console.warn(`Could not read file: ${file}`);
            return '';
          }
        }).join('\n');
        return code;
      }
    }
  };
}

// Custom plugin for chart scripts
function inlineChartScripts() {
  return {
    name: 'inline-chart-scripts',
    resolveId(id) {
      if (id === 'virtual:chart-matrix') {
        return id;
      }
    },
    load(id) {
      if (id === 'virtual:chart-matrix') {
        const files = [
          './resources/legacy/javascript/plugin/chartjs-adapter-date-fns.bundle.min.js',
          './resources/legacy/javascript/plugin/chartjs-chart-matrix.min.js'
        ];
        const code = files.map(file => {
          try {
            return readFileSync(file, 'utf-8');
          } catch (e) {
            console.warn(`Could not read file: ${file}`);
            return '';
          }
        }).join('\n');
        return code;
      }
    }
  };
}

// Custom plugin to clean output directory (images are at public/images, not touched)
function cleanOutputDir() {
  return {
    name: 'clean-output-dir',
    buildStart() {
      const outDir = resolve(__dirname, 'public/legacy');

      try {
        // Remove everything in public/legacy directory
        const items = readdirSync(outDir);
        items.forEach(item => {
          const itemPath = join(outDir, item);
          const stat = statSync(itemPath);
          if (stat.isDirectory()) {
            rmSync(itemPath, { recursive: true, force: true });
          } else {
            rmSync(itemPath, { force: true });
          }
        });
        console.log('✓ Cleaned output directory');
      } catch (e) {
        // Directory might not exist yet, that's ok
        if (e.code !== 'ENOENT') {
          console.warn('Warning during cleanup:', e.message);
        }
      }
    }
  };
}

// Custom plugin to copy assets structure (exact copy)
function copyAssetsStructure() {
  return {
    name: 'copy-assets-structure',
    writeBundle() {
      const assetsJsDir = resolve(__dirname, 'assets/js');
      const assetsCssDir = resolve(__dirname, 'assets/css');
      const publicJsDir = resolve(__dirname, 'public/legacy/js');
      const publicCssDir = resolve(__dirname, 'public/legacy/css');

      try {
        // Copy all items from assets/js to public/legacy/js
        if (existsSync(assetsJsDir)) {
          const items = readdirSync(assetsJsDir);
          items.forEach(item => {
            if (item === '.DS_Store') return;

            const sourcePath = join(assetsJsDir, item);
            const destPath = join(publicJsDir, item);
            const stat = statSync(sourcePath);

            if (stat.isDirectory()) {
              // Copy entire directory
              mkdirSync(destPath, { recursive: true });
              cpSync(sourcePath, destPath, { recursive: true });
            } else if (stat.isFile()) {
              // Skip bundled files that we generate
              const bundledFiles = ['admin.min.js', 'background-process.min.js', 'tinymce.min.js'];
              if (!bundledFiles.includes(item)) {
                cpSync(sourcePath, destPath);
              }
            }
          });
        }

        // Copy all items from assets/css to public/legacy/css
        if (existsSync(assetsCssDir)) {
          const items = readdirSync(assetsCssDir);
          items.forEach(item => {
            if (item === '.DS_Store') return;

            const sourcePath = join(assetsCssDir, item);
            const destPath = join(publicCssDir, item);
            const stat = statSync(sourcePath);

            if (stat.isDirectory()) {
              // Copy entire directory
              mkdirSync(destPath, { recursive: true });
              cpSync(sourcePath, destPath, { recursive: true });
            } else if (stat.isFile()) {
              // Skip bundled files that we generate
              const bundledFiles = ['admin.min.css', 'rtl.min.css', 'frontend.min.css', 'mail.min.css'];
              if (!bundledFiles.includes(item)) {
                cpSync(sourcePath, destPath);
              }
            }
          });
        }

        console.log('✓ Copied assets structure (exact copy)');
      } catch (e) {
        console.error('Failed to copy assets structure:', e.message);
      }
    }
  };
}

// Custom plugin to minify and copy JSON files
function copyJsonAssets() {
  return {
    name: 'copy-json-assets',
    writeBundle() {
      const sourceFile = resolve(__dirname, 'resources/json/source-channels.json');
      const destDir = resolve(__dirname, 'public/json');
      const destFile = resolve(destDir, 'source-channels.json');

      try {
        mkdirSync(destDir, { recursive: true });
        // Read, minify, and write JSON
        const jsonContent = readFileSync(sourceFile, 'utf-8');
        const minified = JSON.stringify(JSON.parse(jsonContent));
        writeFileSync(destFile, minified);
        console.log('✓ Minified and copied source-channels.json to public/json/');
      } catch (e) {
        console.error('Failed to minify source-channels.json:', e);
      }
    }
  };
}

// Custom plugin to move frontend assets to public/frontend
function moveFrontendAssets() {
  return {
    name: 'move-frontend-assets',
    writeBundle() {
      const sourceDir = resolve(__dirname, 'public/legacy/js');
      const frontendDir = resolve(__dirname, 'public/frontend/js');

      try {
        // Create frontend directory
        mkdirSync(frontendDir, { recursive: true });

        // Move tracker files from legacy to frontend
        const trackerFiles = ['tracker.min.js', 'tracker.js'];
        trackerFiles.forEach(file => {
          const sourcePath = join(sourceDir, file);
          const destPath = join(frontendDir, file);

          if (existsSync(sourcePath)) {
            cpSync(sourcePath, destPath);
            rmSync(sourcePath, { force: true });
          }
        });

        console.log('✓ Moved frontend assets to public/frontend/');
      } catch (e) {
        console.error('Failed to move frontend assets:', e.message);
      }
    }
  };
}

export default defineConfig({
  root: resolve(__dirname, 'resources/legacy'),
  publicDir: false,

  plugins: [
    cleanOutputDir(),
    jQueryReadyWrapper(),
    inlineAdminSources(),
    inlineBackgroundProcess(),
    inlineTinyMCE(),
    inlineTrackerScripts(),
    // inlineChartScripts() - Not needed, Chart.js files copied from assets/js/chartjs/
    copyAssetsStructure(),
    copyJsonAssets(),
    moveFrontendAssets(),
  ],

  build: {
    outDir: resolve(__dirname, 'public/legacy'),
    emptyOutDir: false, // We handle cleanup with cleanOutputDir plugin
    minify: 'terser',

    terserOptions: {
      compress: {
        drop_console: false,
      },
      format: {
        comments: false,
      },
    },

    rollupOptions: {
      input: {
        // Admin bundle
        'js/admin.min': resolve(__dirname, 'resources/legacy/entries/admin.js'),

        // Background process
        'js/background-process.min': resolve(__dirname, 'resources/legacy/entries/background-process.js'),

        // TinyMCE
        'js/tinymce.min': resolve(__dirname, 'resources/legacy/entries/tinymce.js'),

        // Tracker (minified) - will be moved to public/frontend/js/ by moveFrontendAssets plugin
        'js/tracker.min': resolve(__dirname, 'resources/frontend/entries/tracker.js'),

        // Mini chart (minified)
        'js/mini-chart.min': resolve(__dirname, 'resources/legacy/entries/mini-chart.js'),

        // Note: chart-matrix.min.js and Chart.js library files are copied from assets/js/chartjs/ as-is

        // Styles
        'css/admin.min': resolve(__dirname, 'resources/legacy/sass/admin.scss'),
        'css/rtl.min': resolve(__dirname, 'resources/legacy/sass/rtl.scss'),
        'css/frontend.min': resolve(__dirname, 'resources/legacy/sass/frontend.scss'),
        'css/mail.min': resolve(__dirname, 'resources/legacy/sass/mail.scss'),
      },

      output: {
        entryFileNames: '[name].js',
        chunkFileNames: 'js/[name].js',
        assetFileNames: (assetInfo) => {
          if (assetInfo.name && assetInfo.name.endsWith('.css')) {
            const name = assetInfo.name.replace('.css', '');
            if (name.includes('admin') || name.includes('rtl') || name.includes('frontend') || name.includes('mail')) {
              return name + '.min.css';
            }
            return '[name][extname]';
          }
          return 'assets/[name][extname]';
        },
      },

      // External dependencies (available globally in WordPress or loaded separately)
      external: [
        'jquery',
        'chart.js',
        'chartjs-adapter-date-fns',
        'chartjs-chart-matrix',
        /^chart\.js/,  // Match any chart.js imports
      ],

      // Prevent code splitting by disabling chunk optimization
      preserveEntrySignatures: 'strict',
    },

    // Disable build optimization that creates shared chunks
    commonjsOptions: {
      transformMixedEsModules: true,
    },

    target: 'es2020', // Support optional chaining and other modern features

    cssMinify: 'lightningcss',
  },

  css: {
    preprocessorOptions: {
      scss: {
        api: 'modern-compiler',
        // Only silence @import warnings for RTL files that require nested imports
        silenceDeprecations: ['import'],
      }
    },
    lightningcss: {
      minify: true,
    }
  },

  resolve: {
    alias: {
      '@': resolve(__dirname, 'resources/legacy'),
      '@assets/json/source-channels.json': resolve(__dirname, 'resources/json/source-channels.json'),
      '@assets/images': resolve(__dirname, 'public/images'),
    }
  }
});
