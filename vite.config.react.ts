import { resolve, dirname } from 'node:path'
import { fileURLToPath } from 'node:url'

import tailwindcss from '@tailwindcss/vite'
import { tanstackRouter } from '@tanstack/router-plugin/vite'
import { wpI18n } from './vite-plugin-wp-i18n.js'
import react from '@vitejs/plugin-react'
import { defineConfig } from 'vite'

import postcssImportantPlugin from './postcss-important-plugin.js'

const __filename = fileURLToPath(import.meta.url)
const __dirname = dirname(__filename)

// https://vite.dev/config/
export default defineConfig(({ mode }) => {
  const reactRoot = resolve(__dirname, 'resources/react')

  return {
    base: './',
    root: reactRoot,
    publicDir: resolve(reactRoot, 'public'),
    plugins: [
      tanstackRouter({
        target: 'react',
        autoCodeSplitting: true,
      }),
      react(),
      tailwindcss(),
      wpI18n({ textDomain: 'wp-statistics' }),
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
