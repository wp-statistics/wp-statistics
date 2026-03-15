import { defineConfig } from 'vite'
import { resolve } from 'path'
import { readFileSync, writeFileSync, mkdirSync, rmSync, readdirSync, cpSync, existsSync } from 'fs'
import { join } from 'path'

// Custom plugin for tracker scripts (bundles frontend tracker files via virtual module)
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
          './resources/entries/tracker/engagement-tracker.js',
          './resources/entries/tracker/batch-queue.js',
          './resources/entries/tracker/user-tracker.js',
          './resources/entries/tracker/event-tracker.js',
          './resources/entries/tracker/tracker.js',
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

// Custom plugin to clean the output directory before each build
function cleanOutputDir() {
  return {
    name: 'clean-output-dir',
    buildStart() {
      const outDir = resolve(__dirname, 'public/entries')

      try {
        if (existsSync(outDir)) {
          rmSync(outDir, { recursive: true, force: true })
          console.log('✓ Cleaned public/entries/')
        }
      } catch (e) {
        if (e.code !== 'ENOENT') {
          console.warn('Warning during cleanup:', e.message)
        }
      }
    },
  }
}

// Custom plugin to minify and copy JSON files from resources/json to public/json
function copyJsonAssets() {
  return {
    name: 'copy-json-assets',
    writeBundle() {
      const sourceDir = resolve(__dirname, 'resources/json')
      const destDir = resolve(__dirname, 'public/json')

      try {
        mkdirSync(destDir, { recursive: true })

        const jsonFiles = readdirSync(sourceDir).filter((file) => file.endsWith('.json'))

        jsonFiles.forEach((file) => {
          const sourcePath = join(sourceDir, file)
          const destFileName = file.replace('.json', '.min.json')
          const destPath = join(destDir, destFileName)

          try {
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

        if (existsSync(destDir)) {
          rmSync(destDir, { recursive: true, force: true })
        }

        mkdirSync(destDir, { recursive: true })
        cpSync(sourceDir, destDir, { recursive: true })

        console.log('✓ Copied image assets from resources/images to public/images')
      } catch (e) {
        console.error('Failed to copy image assets:', e.message)
      }
    },
  }
}

export default defineConfig({
  publicDir: false,

  plugins: [
    cleanOutputDir(),
    copyImageAssets(),
    inlineTrackerScripts(),
    copyJsonAssets(),
  ],

  build: {
    outDir: resolve(__dirname, 'public/entries'),
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
        // Admin bar
        'admin-bar/admin-bar.min': resolve(__dirname, 'resources/entries/admin-bar/admin-bar.js'),
        'admin-bar/admin-bar-style': resolve(__dirname, 'resources/entries/admin-bar/admin-bar.scss'),

        // Dashboard widget
        'dashboard-widget/dashboard-widget-style': resolve(__dirname, 'resources/entries/dashboard-widget/dashboard-widget.scss'),

        // Command palette (WordPress Cmd+K integration)
        'command-palette/command-palette.min': resolve(__dirname, 'resources/entries/command-palette/command-palette.js'),

        // Tracker (bundled from source files)
        'tracker/tracker.min': resolve(__dirname, 'resources/entries/tracker/entry.js'),
      },

      output: {
        entryFileNames: '[name].js',
        chunkFileNames: '[name].js',
        assetFileNames: (assetInfo) => {
          if (assetInfo.name && assetInfo.name.endsWith('.css')) {
            const name = assetInfo.name.replace('.css', '')
            return name + '.min.css'
          }
          return '[name][extname]'
        },
      },

      // Prevent code splitting
      preserveEntrySignatures: 'strict',
    },

    commonjsOptions: {
      transformMixedEsModules: true,
    },

    target: 'es2020',

    cssMinify: 'lightningcss',
  },

  css: {
    preprocessorOptions: {
      scss: {
        api: 'modern-compiler',
        silenceDeprecations: ['import'],
      },
    },
    lightningcss: {
      minify: true,
    },
  },
})
