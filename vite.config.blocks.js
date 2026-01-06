import { defineConfig } from 'vite'
import { resolve } from 'path'
import { readdirSync, statSync, mkdirSync, rmSync, existsSync } from 'fs'

/**
 * Vite configuration for Gutenberg blocks.
 *
 * Builds block assets from resources/blocks/ to public/blocks/
 * Each block has its own directory with index.js and style.scss
 */

// Clean blocks output directory
function cleanBlocksDir() {
  return {
    name: 'clean-blocks-dir',
    buildStart() {
      const outDir = resolve(__dirname, 'public/blocks')

      try {
        if (existsSync(outDir)) {
          rmSync(outDir, { recursive: true, force: true })
        }
        mkdirSync(outDir, { recursive: true })
        console.log('✓ Cleaned public/blocks directory')
      } catch (e) {
        if (e.code !== 'ENOENT') {
          console.warn('Warning during cleanup:', e.message)
        }
      }
    },
  }
}

// Dynamically discover all blocks and create entries
function getBlockEntries() {
  const blocksDir = resolve(__dirname, 'resources/blocks')
  const entries = {}

  if (!existsSync(blocksDir)) {
    return entries
  }

  const blocks = readdirSync(blocksDir)

  blocks.forEach((block) => {
    if (block === '.DS_Store') return

    const blockPath = resolve(blocksDir, block)
    const stat = statSync(blockPath)

    if (stat.isDirectory()) {
      const files = readdirSync(blockPath)

      files.forEach((file) => {
        if (file === 'index.js') {
          entries[`${block}/index`] = resolve(blockPath, file)
        }
        if (file === 'style.scss') {
          entries[`${block}/style`] = resolve(blockPath, file)
        }
        if (file === 'editor.scss') {
          entries[`${block}/editor`] = resolve(blockPath, file)
        }
      })
    }
  })

  return entries
}

export default defineConfig({
  plugins: [cleanBlocksDir()],
  publicDir: false,

  build: {
    outDir: resolve(__dirname, 'public/blocks'),
    emptyOutDir: false,
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
      input: getBlockEntries(),

      output: {
        entryFileNames: '[name].js',
        assetFileNames: (assetInfo) => {
          if (assetInfo.name && assetInfo.name.endsWith('.css')) {
            // Extract block name and file name from the path
            const name = assetInfo.name.replace('.css', '')
            return `${name}.css`
          }
          return '[name][extname]'
        },
      },

      // External WordPress dependencies
      external: ['jquery'],
    },
  },

  css: {
    preprocessorOptions: {
      scss: {
        api: 'modern-compiler',
      },
    },
  },
})
