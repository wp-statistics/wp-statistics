import { defineConfig } from 'vite';
import { resolve } from 'path';
import { readFileSync, writeFileSync, mkdirSync } from 'fs';
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
          './resources/legacy/javascript/user-tracker.js',
          './resources/legacy/javascript/event-tracker.js',
          './resources/legacy/javascript/tracker.js',
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

// Custom plugin to minify and copy JSON files
function copyJsonAssets() {
  return {
    name: 'copy-json-assets',
    writeBundle() {
      const sourceFile = resolve(__dirname, 'resources/legacy/json/source-channels.json');
      const destDir = resolve(__dirname, 'public/legacy/json');
      const destFile = resolve(destDir, 'source-channels.json');

      try {
        mkdirSync(destDir, { recursive: true });
        // Read, minify, and write JSON
        const jsonContent = readFileSync(sourceFile, 'utf-8');
        const minified = JSON.stringify(JSON.parse(jsonContent));
        writeFileSync(destFile, minified);
        console.log('✓ Minified and copied source-channels.json to public/legacy/json/');
      } catch (e) {
        console.error('Failed to minify source-channels.json:', e);
      }
    }
  };
}

export default defineConfig({
  root: resolve(__dirname, 'resources/legacy'),
  publicDir: false,

  plugins: [
    jQueryReadyWrapper(),
    inlineAdminSources(),
    inlineBackgroundProcess(),
    inlineTinyMCE(),
    inlineTrackerScripts(),
    inlineChartScripts(),
    copyJsonAssets(),
  ],

  build: {
    outDir: resolve(__dirname, 'public/legacy'),
    emptyOutDir: true,
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

        // Tracker (minified)
        'js/tracker.min': resolve(__dirname, 'resources/legacy/entries/tracker.js'),

        // Mini chart (minified)
        'js/mini-chart.min': resolve(__dirname, 'resources/legacy/entries/mini-chart.js'),

        // Chart matrix
        'js/chartjs/chart-matrix.min': resolve(__dirname, 'resources/legacy/entries/chart-matrix.js'),

        // Select2
        'js/select2.min': resolve(__dirname, 'resources/legacy/entries/select2.js'),

        // Datepicker
        'js/datepicker.min': resolve(__dirname, 'resources/legacy/entries/datepicker.js'),

        // JQVMap
        'js/jqvmap.min': resolve(__dirname, 'resources/legacy/entries/jqvmap.js'),

        // Styles
        'css/admin.min': resolve(__dirname, 'resources/legacy/sass/admin.scss'),
        'css/rtl.min': resolve(__dirname, 'resources/legacy/sass/rtl.scss'),
        'css/frontend.min': resolve(__dirname, 'resources/legacy/sass/frontend.scss'),
      },

      output: {
        entryFileNames: '[name].js',
        chunkFileNames: '[name].js',
        assetFileNames: (assetInfo) => {
          if (assetInfo.name && assetInfo.name.endsWith('.css')) {
            // Extract the base name from the asset
            // For 'css/admin.css' from input 'css/admin.min', we want 'css/admin.min.css'
            const name = assetInfo.name.replace('.css', '');
            if (name.includes('admin') || name.includes('rtl') || name.includes('frontend')) {
              return name + '.min.css';
            }
            // For vendor libraries from js entries, output as css/{library}.min.css
            if (name.includes('select2')) {
              return 'css/select2.min.css';
            }
            if (name.includes('datepicker') || name.includes('daterangepicker') || name.includes('customize')) {
              return 'css/datepicker.min.css';
            }
            if (name.includes('jqvmap')) {
              return 'css/jqvmap.min.css';
            }
            return '[name][extname]';
          }
          return 'assets/[name][extname]';
        },
      },

      // External dependencies (available globally in WordPress)
      external: ['jquery'],
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
      '@assets/json/source-channels.json': resolve(__dirname, 'resources/legacy/json/source-channels.json'),
      '@assets/js/datepicker': resolve(__dirname, 'resources/legacy/vendor/datepicker'),
      '@assets/js/jqvmap': resolve(__dirname, 'resources/legacy/vendor/jqvmap'),
      '@assets/css/datepicker': resolve(__dirname, 'resources/legacy/vendor/datepicker/css'),
      '@assets/css/jqvmap': resolve(__dirname, 'resources/legacy/vendor/jqvmap/css'),
    }
  }
});
