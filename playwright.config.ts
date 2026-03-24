import { defineConfig, devices } from '@playwright/test'

/**
 * Playwright E2E Test Configuration for WP Statistics
 *
 * Uses wp-env (Docker) for an isolated WordPress test environment.
 *
 *   pnpm test:e2e:start   # Start the Docker environment
 *   pnpm test:e2e          # Run tests
 *   pnpm test:e2e:stop    # Stop the Docker environment
 *
 * @see https://playwright.dev/docs/test-configuration
 */

export default defineConfig({
  testDir: './tests/e2e',
  outputDir: './tests/test-results',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: 1,
  reporter: [
    ['html', { outputFolder: 'tests/e2e/reports' }],
    ['list'],
  ],
  use: {
    baseURL: process.env.WP_BASE_URL || 'http://localhost:8888',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
  },

  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
    {
      name: 'firefox',
      use: { ...devices['Desktop Firefox'] },
    },
    {
      name: 'webkit',
      use: { ...devices['Desktop Safari'] },
    },
  ],
})
