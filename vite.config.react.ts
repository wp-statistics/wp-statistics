import { resolve, dirname } from 'node:path'
import { fileURLToPath } from 'node:url'
import { cpSync, rmSync, readFileSync, existsSync, mkdirSync, readdirSync, writeFileSync } from 'node:fs'

import tailwindcss from '@tailwindcss/vite'
import { tanstackRouter } from '@tanstack/router-plugin/vite'
import { wpI18n } from './vite-plugin-wp-i18n.js'
import react from '@vitejs/plugin-react'
import { defineConfig, type Plugin } from 'vite'

import postcssImportantPlugin from './postcss-important-plugin.js'

const __filename = fileURLToPath(import.meta.url)
const __dirname = dirname(__filename)

// Function to load environment files
// Supports both unified .env and legacy .env.react files
function loadReactEnv(mode: string): Record<string, string> {
  // Files are loaded in order, later files override earlier ones
  const envFiles = [
    `.env`,                    // Base env (lowest priority)
    `.env.${mode}`,            // Mode-specific
    `.env.local`,              // Local overrides
    `.env.${mode}.local`,      // Mode-specific local
    `.env.react`,              // Legacy: React-specific (for backward compatibility)
    `.env.react.${mode}`,      // Legacy: Mode-specific
    `.env.react.local`,        // Legacy: Local overrides (gitignored)
    `.env.react.${mode}.local` // Highest priority (mode-specific local)
  ]

  const env: Record<string, string> = {}

  for (const file of envFiles) {
    const filePath = resolve(__dirname, file)
    if (existsSync(filePath)) {
      const content = readFileSync(filePath, 'utf-8')
      const lines = content.split('\n')

      for (const line of lines) {
        const trimmed = line.trim()
        // Skip empty lines and comments
        if (!trimmed || trimmed.startsWith('#')) continue

        const match = trimmed.match(/^([^=]+)=(.*)$/)
        if (match) {
          const key = match[1].trim()
          let value = match[2].trim()

          // Remove quotes if present
          if ((value.startsWith('"') && value.endsWith('"')) ||
              (value.startsWith("'") && value.endsWith("'"))) {
            value = value.slice(1, -1)
          }

          env[key] = value
        }
      }
    }
  }

  return env
}

function copyImagesPlugin(): Plugin {
  return {
    name: 'copy-images',
    writeBundle() {
      const src = resolve(__dirname, 'resources/images')
      const dest = resolve(__dirname, 'public/images')
      rmSync(dest, { recursive: true, force: true })
      cpSync(src, dest, { recursive: true })
    },
  }
}

/**
 * Vite plugin to copy and minify GeoJSON files from resources to public
 * GeoJSON files are minified by parsing and re-stringifying without whitespace
 */
function copyGeoJsonPlugin(): Plugin {
  return {
    name: 'copy-geojson',
    writeBundle() {
      const src = resolve(__dirname, 'resources/json/geojson')
      const dest = resolve(__dirname, 'public/geojson')

      // Skip if source directory doesn't exist
      if (!existsSync(src)) {
        return
      }

      // Clean and recreate destination
      rmSync(dest, { recursive: true, force: true })
      mkdirSync(dest, { recursive: true })

      // Copy and minify each GeoJSON file
      const files = readdirSync(src).filter(f => f.endsWith('.geojson'))
      for (const file of files) {
        const content = readFileSync(resolve(src, file), 'utf-8')
        // Minify by parsing and stringifying without whitespace
        const minified = JSON.stringify(JSON.parse(content))
        // Output with .min.geojson extension
        const outName = file.replace('.geojson', '.min.geojson')
        writeFileSync(resolve(dest, outName), minified)
      }
    },
  }
}

// https://vite.dev/config/
export default defineConfig(({ mode }) => {
  const reactRoot = resolve(__dirname, 'resources/react')

  // Load environment variables from .env and .env.react files
  const env = loadReactEnv(mode)

  // Get dev server URL from env (only required for development mode)
  const devServerUrl = env.VITE_DEV_SERVER_URL

  // Only require VITE_DEV_SERVER_URL in development mode
  // Production builds don't use the dev server
  if (!devServerUrl && mode === 'development') {
    throw new Error('VITE_DEV_SERVER_URL is not configured. Please set it in .env or .env.local')
  }

  // Extract port from URL (default to 5173 for production builds)
  const urlMatch = devServerUrl?.match(/:(\d+)/)
  const port = urlMatch ? parseInt(urlMatch[1]) : 5173

  return {
    base: mode === 'development' ? '/' : './',
    root: reactRoot,
    publicDir: false,
    server: {
      port: port,
      strictPort: true,
      cors: true,
      origin: devServerUrl || `http://localhost:${port}`,
    },
    plugins: [
      tanstackRouter({
        target: 'react',
        autoCodeSplitting: true,
      }),
      react(),
      tailwindcss(),
      wpI18n({ textDomain: 'wp-statistics' }),
      copyImagesPlugin(),
      copyGeoJsonPlugin(),
    ],
    css: {
      postcss: {
        plugins: [postcssImportantPlugin()],
      },
    },
    build: {
      outDir: resolve(__dirname, 'public/react'),
      emptyOutDir: true,
      manifest: true,
      minify: mode === 'production',
      sourcemap: mode === 'development' ? true : false,
      rollupOptions: {
        input: {
          main: resolve(reactRoot, 'src/main.tsx'),
        },
      },
    },
    resolve: {
      alias: {
        '@': resolve(reactRoot, 'src'),
        '@components': resolve(reactRoot, 'src/components'),
        '@hooks': resolve(reactRoot, 'src/hooks'),
        '@lib': resolve(reactRoot, 'src/lib'),
        '@services': resolve(reactRoot, 'src/services'),
        '@types': resolve(reactRoot, 'src/types'),
        '@stores': resolve(reactRoot, 'src/stores'),
        '@pages': resolve(reactRoot, 'src/pages'),
        '@routes': resolve(reactRoot, 'src/routes'),
      },
      dedupe: ['react', 'react-dom'],
    },
    optimizeDeps: {
      include: ['react', 'react-dom', 'lucide-react', 'axios', '@tanstack/react-query'],
      exclude: ['@wordpress/element'],
      force: false,
    },
  }
})
