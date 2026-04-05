import { resolve, dirname } from 'node:path'
import { fileURLToPath } from 'node:url'
import { defineConfig } from 'vitest/config'
import react from '@vitejs/plugin-react'
import { storybookTest } from '@storybook/addon-vitest/vitest-plugin'
import { playwright } from '@vitest/browser-playwright'

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
    // Vitest 4.x uses projects instead of workspace
    projects: [
      // Unit tests (this config)
      {
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
        test: {
          name: 'unit',
          environment: 'jsdom',
          setupFiles: ['./vitest.setup.ts'],
          include: ['tests/unit/react/**/*.{test,spec}.{js,ts,jsx,tsx}'],
        },
      },
      // Storybook tests
      {
        plugins: [
          storybookTest({
            configDir: resolve(__dirname, '.storybook'),
          }),
        ],
        test: {
          name: 'storybook',
          browser: {
            enabled: true,
            headless: true,
            provider: playwright(),
            instances: [{ browser: 'chromium' }],
          },
          // Don't use include - Storybook plugin uses stories from .storybook/main.ts
          setupFiles: ['.storybook/vitest.setup.ts'],
        },
      },
    ],
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
