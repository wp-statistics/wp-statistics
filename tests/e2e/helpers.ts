import { Page, expect } from '@playwright/test'

/**
 * WordPress admin credentials for testing
 * Set WP_ADMIN_USER and WP_ADMIN_PASS in .env or .env.local
 */
export const ADMIN_USER = process.env.WP_ADMIN_USER || 'admin'
export const ADMIN_PASS = process.env.WP_ADMIN_PASS || 'password'

/**
 * WP Statistics admin page URLs
 */
export const PAGES = {
  overview: '/wp-admin/admin.php?page=wp-statistics',
  visitors: '/wp-admin/admin.php?page=wp-statistics#/visitors',
  pages: '/wp-admin/admin.php?page=wp-statistics#/pages',
  referrals: '/wp-admin/admin.php?page=wp-statistics#/referrals',
  geographic: '/wp-admin/admin.php?page=wp-statistics#/geographic',
  devices: '/wp-admin/admin.php?page=wp-statistics#/devices',
  settings: '/wp-admin/admin.php?page=wp-statistics#/settings',
  tools: '/wp-admin/admin.php?page=wp-statistics#/tools',
  diagnostics: '/wp-admin/admin.php?page=wp-statistics#/tools/diagnostics',
}

/**
 * Login to WordPress admin
 * Handles both regular login and wp-now auto-login scenarios
 */
export async function loginToWordPress(page: Page): Promise<void> {
  // Go to wp-admin - this will either show dashboard (if auto-logged in) or redirect to login
  await page.goto('/wp-admin/')
  await page.waitForLoadState('domcontentloaded')

  // Check if we're already logged in (wp-now auto-login) or need to log in
  const currentUrl = page.url()
  if (currentUrl.includes('/wp-admin/') && !currentUrl.includes('wp-login')) {
    // Already logged in (wp-now auto-login), nothing to do
    return
  }

  // Not logged in, need to use the login form
  // Wait for login form to be ready
  await page.waitForSelector('#user_login', { state: 'visible', timeout: 10000 })

  // Clear and fill credentials
  await page.fill('#user_login', '')
  await page.fill('#user_login', ADMIN_USER)
  await page.fill('#user_pass', '')
  await page.fill('#user_pass', ADMIN_PASS)

  // Submit and wait for navigation
  await page.click('#wp-submit')
  await page.waitForURL(/\/wp-admin\//, { timeout: 15000 })
}

/**
 * Navigate to a WP Statistics page
 */
export async function navigateToPage(page: Page, pageKey: keyof typeof PAGES): Promise<void> {
  await page.goto(PAGES[pageKey])
  // Wait for React app to load (uses id="wp-statistics-app" or legacy .wps-wrap)
  await page.waitForSelector('#wp-statistics-app, .wps-wrap', { timeout: 30000 })
}

/**
 * Wait for the React app to be fully loaded
 */
export async function waitForAppLoad(page: Page): Promise<void> {
  // Wait for loading states to complete
  await page.waitForLoadState('networkidle')
  // Wait for any skeleton loaders to disappear
  await page.waitForFunction(() => {
    const skeletons = document.querySelectorAll('[class*="skeleton"]')
    return skeletons.length === 0
  }, { timeout: 30000 }).catch(() => {
    // Skeletons might not exist, that's okay
  })
}

/**
 * Check if an element is visible
 */
export async function isVisible(page: Page, selector: string): Promise<boolean> {
  try {
    await page.waitForSelector(selector, { state: 'visible', timeout: 5000 })
    return true
  } catch {
    return false
  }
}

/**
 * Take a screenshot with a descriptive name
 */
export async function takeScreenshot(page: Page, name: string): Promise<void> {
  await page.screenshot({ path: `tests/e2e/screenshots/${name}.png`, fullPage: true })
}

/**
 * Check page for console errors
 */
export async function checkNoConsoleErrors(page: Page): Promise<string[]> {
  const errors: string[] = []
  page.on('console', (msg) => {
    if (msg.type() === 'error') {
      errors.push(msg.text())
    }
  })
  return errors
}

/**
 * Verify tracker script is present on the page
 */
export async function verifyTrackerScript(page: Page): Promise<boolean> {
  const scripts = await page.$$eval('script', (scripts) =>
    scripts.map((s) => s.src || s.textContent?.substring(0, 100))
  )
  return scripts.some((s) => s?.includes('wp-statistics') || s?.includes('wps_'))
}
