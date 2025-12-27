import { resolve, dirname } from 'node:path'
import { fileURLToPath } from 'node:url'
import { cpSync, rmSync, readFileSync, existsSync } from 'node:fs'

import tailwindcss from '@tailwindcss/vite'
import { tanstackRouter } from '@tanstack/router-plugin/vite'
import { wpI18n } from './vite-plugin-wp-i18n.js'
import react from '@vitejs/plugin-react'
import { defineConfig, type Plugin } from 'vite'

import postcssImportantPlugin from './postcss-important-plugin.js'

const __filename = fileURLToPath(import.meta.url)
const __dirname = dirname(__filename)

// Function to load custom .env.react file
function loadReactEnv(mode: string): Record<string, string> {
  // Files are loaded in order, later files override earlier ones
  const envFiles = [
    `.env.react`,              // Lowest priority (committed to git)
    `.env.react.${mode}`,      // Mode-specific
    `.env.react.local`,        // Local overrides (gitignored)
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

// https://vite.dev/config/
export default defineConfig(({ mode }) => {
  const reactRoot = resolve(__dirname, 'resources/react')

  // Load environment variables from .env.react files
  const env = loadReactEnv(mode)

  // Get dev server URL from env (configured in .env.react or .env.react.local)
  const devServerUrl = env.VITE_DEV_SERVER_URL

  if (!devServerUrl) {
    throw new Error('VITE_DEV_SERVER_URL is not configured. Please set it in .env.react or .env.react.local')
  }

  // Extract port from URL
  const urlMatch = devServerUrl.match(/:(\d+)/)
  const port = urlMatch ? parseInt(urlMatch[1]) : 5173

  return {
    base: mode === 'development' ? '/' : './',
    root: reactRoot,
    publicDir: false,
    server: {
      port: port,
      strictPort: true,
      cors: true,
      origin: devServerUrl,
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
