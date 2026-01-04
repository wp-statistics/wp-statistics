import { defineConfig } from 'vite'
import { resolve } from 'path'
import { readFileSync, writeFileSync, mkdirSync, rmSync, readdirSync, statSync, cpSync, existsSync } from 'fs'
import { join } from 'path'
import { globSync } from 'glob'

// Custom plugin to wrap JS in jQuery ready
function jQueryReadyWrapper() {
  return {
    name: 'jquery-ready-wrapper',
    generateBundle(options, bundle) {
      for (const fileName in bundle) {
        if (bundle[fileName].type === 'chunk' && fileName.endsWith('.js')) {
          const chunk = bundle[fileName]
          if (fileName.includes('admin.min.js') || fileName.includes('background-process.min.js')) {
            chunk.code = `jQuery(document).ready(function ($) {${chunk.code}});`
          }
        }
      }
    },
  }
}

// Custom plugin to inline source files for admin bundle
function inlineAdminSources() {
  return {
    name: 'inline-admin-sources',
    resolveId(id) {
      if (id === 'virtual:admin-bundle') {
        return id
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
        ]

        const resolvedFiles = files.flatMap((pattern) => globSync(pattern))
        const code = resolvedFiles
          .map((file) => {
            try {
              let content = readFileSync(file, 'utf-8')

              // Patch UMD wrappers to prevent require('jquery') errors
              if (file.includes('tooltipster.bundle.js') || file.includes('ajaxq.js')) {
                content = content.replace(
                  /module\.exports\s*=\s*factory\(require\(['"]jquery['"]\)\);?/g,
                  'factory(jQuery);'
                )
              }

              // Patch UMD wrappers to prevent require('chart.js') errors
              if (file.includes('chartjs-adapter-date-fns.bundle.min.js')) {
                content = content.replace(
                  /e\(require\("chart\.js"\)\)/g,
                  'e((t="undefined"!=typeof globalThis?globalThis:t||self).Chart)'
                )
              }

              if (file.includes('chartjs-chart-matrix.min.js')) {
                content = content.replace(
                  /e\(exports,\s*require\("chart\.js"\),\s*require\("chart\.js\/helpers"\)\)/g,
                  'e((t = "undefined" != typeof globalThis ? globalThis : t || self)["chartjs-chart-matrix"] = {}, t.Chart, t.Chart.helpers)'
                )
              }

              return content
            } catch (e) {
              console.warn(`Could not read file: ${file}`)
              return ''
            }
          })
          .join('\n')

        return code
      }
    },
  }
}

// Custom plugin for background process
function inlineBackgroundProcess() {
  return {
    name: 'inline-background-process',
    resolveId(id) {
      if (id === 'virtual:background-process') {
        return id
      }
    },
    load(id) {
      if (id === 'virtual:background-process') {
        try {
          return readFileSync('./resources/legacy/javascript/background-process.js', 'utf-8')
        } catch (e) {
          console.warn('Could not read background-process.js')
          return ''
        }
      }
    },
  }
}

// Custom plugin for TinyMCE
function inlineTinyMCE() {
  return {
    name: 'inline-tinymce',
    resolveId(id) {
      if (id === 'virtual:tinymce') {
        return id
      }
    },
    load(id) {
      if (id === 'virtual:tinymce') {
        const files = globSync('./resources/legacy/javascript/Tinymce/*.js')
        const code = files
          .map((file) => {
            try {
              return readFileSync(file, 'utf-8')
            } catch (e) {
              console.warn(`Could not read file: ${file}`)
              return ''
            }
          })
          .join('\n')
        return code
      }
    },
  }
}

// Custom plugin for tracker scripts
function inlineTrackerScripts() {
  return {
    name: 'inline-tracker',
    resolveId(id) {
      if (id === 'virtual:tracker') {
        return id
      }
    },
    load(id) {
      if (id === 'virtual:tracker') {
        const files = [
          './resources/frontend/js/engagement-tracker.js',
          './resources/frontend/js/batch-queue.js',
          './resources/frontend/js/user-tracker.js',
          './resources/frontend/js/event-tracker.js',
          './resources/frontend/js/tracker.js',
        ]
        const code = files
          .map((file) => {
            try {
              return readFileSync(file, 'utf-8')
            } catch (e) {
              console.warn(`Could not read file: ${file}`)
              return ''
            }
          })
          .join('\n')
        return code
      }
    },
  }
}

// Custom plugin for chart scripts
function inlineChartScripts() {
  return {
    name: 'inline-chart-scripts',
    resolveId(id) {
      if (id === 'virtual:chart-matrix') {
        return id
      }
    },
    load(id) {
      if (id === 'virtual:chart-matrix') {
        const files = [
          './resources/legacy/javascript/plugin/chartjs-adapter-date-fns.bundle.min.js',
          './resources/legacy/javascript/plugin/chartjs-chart-matrix.min.js',
        ]
        const code = files
          .map((file) => {
            try {
              return readFileSync(file, 'utf-8')
            } catch (e) {
              console.warn(`Could not read file: ${file}`)
              return ''
            }
          })
          .join('\n')
        return code
      }
    },
  }
}

// Custom plugin to clean output directory (images are at public/images, not touched)
function cleanOutputDir() {
  return {
    name: 'clean-output-dir',
    buildStart() {
      const outDir = resolve(__dirname, 'public/legacy')

      try {
        // Remove everything in public/legacy directory
        const items = readdirSync(outDir)
        items.forEach((item) => {
          const itemPath = join(outDir, item)
          const stat = statSync(itemPath)
          if (stat.isDirectory()) {
            rmSync(itemPath, { recursive: true, force: true })
          } else {
            rmSync(itemPath, { force: true })
          }
        })
        console.log('✓ Cleaned output directory')
      } catch (e) {
        // Directory might not exist yet, that's ok
        if (e.code !== 'ENOENT') {
          console.warn('Warning during cleanup:', e.message)
        }
      }
    },
  }
}

// Custom plugin to copy vendor files from resources/legacy/vendor
// Properly separates JS and CSS files into their respective directories
function copyVendorFiles() {
  return {
    name: 'copy-vendor-files',
    writeBundle() {
      const vendorDir = resolve(__dirname, 'resources/legacy/vendor')
      const publicJsDir = resolve(__dirname, 'public/legacy/js')
      const publicCssDir = resolve(__dirname, 'public/legacy/css')

      try {
        if (!existsSync(vendorDir)) {
          console.warn('Vendor directory not found:', vendorDir)
          return
        }

        const copyFilesRecursively = (sourceDir, destJsDir, destCssDir, relativePath = '') => {
          const items = readdirSync(sourceDir)

          items.forEach((item) => {
            if (item === '.DS_Store') return

            const sourcePath = join(sourceDir, item)
            const stat = statSync(sourcePath)

            if (stat.isDirectory()) {
              // Recursively process subdirectories
              copyFilesRecursively(
                sourcePath,
                destJsDir,
                destCssDir,
                join(relativePath, item)
              )
            } else {
              // Copy files based on extension
              const ext = item.substring(item.lastIndexOf('.'))

              if (ext === '.js') {
                // Copy JS files to js directory
                const destPath = join(destJsDir, relativePath, item)
                mkdirSync(join(destJsDir, relativePath), { recursive: true })
                cpSync(sourcePath, destPath)
              } else if (ext === '.css') {
                // Copy CSS files to css directory
                const destPath = join(destCssDir, relativePath, item)
                mkdirSync(join(destCssDir, relativePath), { recursive: true })
                cpSync(sourcePath, destPath)
              } else {
                // Copy other files (images, fonts, etc.) to both directories
                // This preserves directory structure for assets that might be referenced by both
                const destJsPath = join(destJsDir, relativePath, item)
                const destCssPath = join(destCssDir, relativePath, item)
                mkdirSync(join(destJsDir, relativePath), { recursive: true })
                mkdirSync(join(destCssDir, relativePath), { recursive: true })
                cpSync(sourcePath, destJsPath)
                cpSync(sourcePath, destCssPath)
              }
            }
          })
        }

        const items = readdirSync(vendorDir)
        items.forEach((item) => {
          if (item === '.DS_Store') return

          const sourcePath = join(vendorDir, item)
          const stat = statSync(sourcePath)

          if (stat.isDirectory()) {
            const destJsPath = join(publicJsDir, item)
            const destCssPath = join(publicCssDir, item)

            // Process each vendor directory
            copyFilesRecursively(sourcePath, destJsPath, destCssPath)
          }
        })

        console.log('✓ Copied vendor files from resources/legacy/vendor (JS and CSS properly separated)')
      } catch (e) {
        console.error('Failed to copy vendor files:', e.message)
      }
    },
  }
}

// Custom plugin to minify and copy JSON files
function copyJsonAssets() {
  return {
    name: 'copy-json-assets',
    writeBundle() {
      const sourceDir = resolve(__dirname, 'resources/json')
      const destDir = resolve(__dirname, 'public/json')

      try {
        mkdirSync(destDir, { recursive: true })

        // Get all JSON files from source directory
        const jsonFiles = readdirSync(sourceDir).filter((file) => file.endsWith('.json'))

        jsonFiles.forEach((file) => {
          const sourcePath = join(sourceDir, file)
          const destFileName = file.replace('.json', '.min.json')
          const destPath = join(destDir, destFileName)

          try {
            // Read, minify, and write JSON
            const jsonContent = readFileSync(sourcePath, 'utf-8')
            const minified = JSON.stringify(JSON.parse(jsonContent))
            writeFileSync(destPath, minified)
            console.log(`✓ Minified and copied ${file} to public/json/${destFileName}`)
          } catch (e) {
            console.error(`Failed to minify ${file}:`, e.message)
          }
        })
      } catch (e) {
        console.error('Failed to copy JSON assets:', e.message)
      }
    },
  }
}

// Custom plugin to copy image assets from resources/images to public/images
function copyImageAssets() {
  return {
    name: 'copy-image-assets',
    buildStart() {
      const sourceDir = resolve(__dirname, 'resources/images')
      const destDir = resolve(__dirname, 'public/images')

      try {
        if (!existsSync(sourceDir)) {
          console.warn('Resources images directory not found:', sourceDir)
          return
        }

        // Remove existing public/images directory
        if (existsSync(destDir)) {
          rmSync(destDir, { recursive: true, force: true })
        }

        // Copy all images from resources to public
        mkdirSync(destDir, { recursive: true })
        cpSync(sourceDir, destDir, { recursive: true })

        console.log('✓ Copied image assets from resources/images to public/images')
      } catch (e) {
        console.error('Failed to copy image assets:', e.message)
      }
    },
  }
}

// Custom plugin to move frontend assets to public/frontend
function moveFrontendAssets() {
  return {
    name: 'move-frontend-assets',
    writeBundle() {
      const sourceJsDir = resolve(__dirname, 'public/legacy/js')
      const sourceCssDir = resolve(__dirname, 'public/legacy/css')
      const frontendJsDir = resolve(__dirname, 'public/frontend/js')
      const frontendCssDir = resolve(__dirname, 'public/frontend/css')

      try {
        // Create frontend directories
        mkdirSync(frontendJsDir, { recursive: true })
        mkdirSync(frontendCssDir, { recursive: true })

        // Move frontend JS files from legacy to frontend
        const jsFiles = ['tracker.min.js', 'tracker.js', 'mini-chart.min.js']
        jsFiles.forEach((file) => {
          const sourcePath = join(sourceJsDir, file)
          const destPath = join(frontendJsDir, file)

          if (existsSync(sourcePath)) {
            cpSync(sourcePath, destPath)
            rmSync(sourcePath, { force: true })
          }
        })

        // Also copy tracker.min.js to assets/js/tracker.js for backward compatibility
        const trackerSource = join(frontendJsDir, 'tracker.min.js')
        const trackerDest = resolve(__dirname, 'assets/js/tracker.js')
        if (existsSync(trackerSource)) {
          cpSync(trackerSource, trackerDest)
        }

        // Copy only chart.umd.min.js from chartjs directory to frontend/js/chartjs/ (others stay in legacy)
        const chartjsSourceDir = join(sourceJsDir, 'chartjs')
        if (existsSync(chartjsSourceDir)) {
          const frontendChartjsDir = join(frontendJsDir, 'chartjs')
          mkdirSync(frontendChartjsDir, { recursive: true })

          const chartUmdMinPath = join(chartjsSourceDir, 'chart.umd.min.js')
          const chartUmdMinDest = join(frontendChartjsDir, 'chart.umd.min.js')

          if (existsSync(chartUmdMinPath)) {
            cpSync(chartUmdMinPath, chartUmdMinDest)
          }
        }

        // Move frontend CSS files from legacy to frontend
        const cssFiles = ['frontend.min.css']
        cssFiles.forEach((file) => {
          const sourcePath = join(sourceCssDir, file)
          const destPath = join(frontendCssDir, file)

          if (existsSync(sourcePath)) {
            cpSync(sourcePath, destPath)
            rmSync(sourcePath, { force: true })
          }
        })

        console.log('✓ Moved frontend assets to public/frontend/')
      } catch (e) {
        console.error('Failed to move frontend assets:', e.message)
      }
    },
  }
}

export default defineConfig({
  root: resolve(__dirname, 'resources/legacy'),
  publicDir: false,

  plugins: [
    cleanOutputDir(),
    copyImageAssets(),
    jQueryReadyWrapper(),
    inlineAdminSources(),
    inlineBackgroundProcess(),
    inlineTinyMCE(),
    inlineTrackerScripts(),
    // inlineChartScripts() - Not needed, Chart.js files copied from resources/legacy/vendor/chartjs/
    copyVendorFiles(),
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

        // Note: chart-matrix.min.js and Chart.js library files are copied from resources/legacy/vendor/chartjs/ as-is

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
            const name = assetInfo.name.replace('.css', '')
            if (name.includes('admin') || name.includes('rtl') || name.includes('frontend') || name.includes('mail')) {
              return name + '.min.css'
            }
            return '[name][extname]'
          }
          return '[name][extname]'
        },
      },

      // External dependencies (available globally in WordPress or loaded separately)
      external: [
        'jquery',
        'chart.js',
        'chartjs-adapter-date-fns',
        'chartjs-chart-matrix',
        /^chart\.js/, // Match any chart.js imports
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
      },
    },
    lightningcss: {
      minify: true,
    },
  },

  resolve: {
    alias: {
      '@': resolve(__dirname, 'resources/legacy'),
      '@assets/json/source-channels.json': resolve(__dirname, 'resources/json/source-channels.json'),
      '@assets/images': resolve(__dirname, 'public/images'),
    },
  },
})
