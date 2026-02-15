import { defineConfig, devices } from '@playwright/test'
import { existsSync, readFileSync } from 'node:fs'
import { resolve, dirname } from 'node:path'
import { fileURLToPath } from 'node:url'

const __filename = fileURLToPath(import.meta.url)
const __dirname = dirname(__filename)

/**
 * Playwright E2E Test Configuration for WP Statistics
 *
 * Configure WP_BASE_URL in .env or .env.local:
 *
 *   # For wp-now (default port 8881)
 *   WP_BASE_URL=http://localhost:8881
 *
 *   # For MAMP/Local/custom setup
 *   WP_BASE_URL=http://localhost:9323
 *
 * Then run: npm run test:e2e
 *
 * @see https://playwright.dev/docs/test-configuration
 */

// Load environment variables from .env files
function loadEnvFiles(): void {
  const envFiles = ['.env', '.env.local']
  for (const file of envFiles) {
    const filePath = resolve(__dirname, file)
    if (existsSync(filePath)) {
      const content = readFileSync(filePath, 'utf-8')
      const lines = content.split('\n')
      for (const line of lines) {
        const trimmed = line.trim()
        if (!trimmed || trimmed.startsWith('#')) continue
        const match = trimmed.match(/^([^=]+)=(.*)$/)
        if (match) {
          const key = match[1].trim()
          let value = match[2].trim().replace(/["']/g, '')
          // Only set if not already in environment
          if (!process.env[key]) {
            process.env[key] = value
          }
        }
      }
    }
  }
}

// Load env files before config
loadEnvFiles()

// Get base URL from environment
function loadBaseUrl(): string {
  return process.env.WP_BASE_URL || 'http://localhost:8881'
}

export default defineConfig({
  testDir: './tests/e2e',
  outputDir: './tests/test-results',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  // Use 1 worker to avoid WordPress session conflicts during login
  // For parallel tests, consider implementing Playwright's auth state storage
  workers: 1,
  reporter: [
    ['html', { outputFolder: 'tests/e2e/reports' }],
    ['list'],
  ],
  use: {
    baseURL: loadBaseUrl(),
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    // Accept self-signed certificates for local HTTPS
    ignoreHTTPSErrors: true,
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

  /*
   * Run wp-now server before starting the tests
   * In CI: The workflow starts wp-now before running tests, so we disable webServer
   * Locally: No automatic server - use wp-now or your preferred local setup
   */
  webServer: undefined,
})
