import { defineWorkspace } from 'vitest/config'
import { storybookTest } from '@storybook/addon-vitest/vitest-plugin'
import { resolve, dirname } from 'node:path'
import { fileURLToPath } from 'node:url'

const __filename = fileURLToPath(import.meta.url)
const __dirname = dirname(__filename)

export default defineWorkspace([
  // Main test config
  'vitest.config.ts',
  // Storybook tests
  {
    extends: 'vitest.config.ts',
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
        name: 'chromium',
        provider: 'playwright',
      },
      include: ['**/*.stories.?(m)[jt]s?(x)'],
      setupFiles: ['.storybook/vitest.setup.ts'],
    },
  },
])
