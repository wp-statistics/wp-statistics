import { resolve, dirname } from 'node:path'
import { fileURLToPath } from 'node:url'
import { defineConfig } from 'vitest/config'
import react from '@vitejs/plugin-react'

const __filename = fileURLToPath(import.meta.url)
const __dirname = dirname(__filename)

const reactRoot = resolve(__dirname, 'resources/react')

export default defineConfig({
  plugins: [react()],
  test: {
    globals: true,
    environment: 'jsdom',
    setupFiles: ['./vitest.setup.ts'],
    include: ['tests/unit/react/**/*.{test,spec}.{js,ts,jsx,tsx}'],
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html'],
      include: ['resources/react/src/**/*.{js,ts,jsx,tsx}'],
      exclude: [
        'resources/react/src/**/*.stories.{js,ts,jsx,tsx}',
        'resources/react/src/**/*.d.ts',
        'resources/react/src/routeTree.gen.ts',
      ],
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
    },
  },
})
