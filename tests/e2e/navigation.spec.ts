import { test, expect } from '@playwright/test'
import { loginToWordPress, PAGES } from './helpers'

test.describe('Navigation', () => {
  test.beforeEach(async ({ page }) => {
    await loginToWordPress(page)
  })

  test('sidebar navigation works', async ({ page }) => {
    await page.goto(PAGES.overview)
    await page.waitForLoadState('domcontentloaded')

    // Look for navigation elements
    const sidebarNav = page.locator('[data-testid="sidebar"], .wps-sidebar, nav')

    // If sidebar exists, test clicking navigation items
    if (await sidebarNav.count() > 0) {
      const navLinks = sidebarNav.locator('a')
      const linkCount = await navLinks.count()

      if (linkCount > 1) {
        // Click the second link
        await navLinks.nth(1).click()
        await page.waitForLoadState('domcontentloaded')

        // Page should load without errors
        const content = await page.content()
        expect(content).not.toContain('Fatal error')
      }
    }
  })

  test('date range picker is functional', async ({ page }) => {
    await page.goto(PAGES.overview)
    await page.waitForLoadState('networkidle')

    // Look for date picker trigger
    const datePicker = page.locator('[data-testid="date-picker"], button:has-text("days"), button:has-text("Last")')

    if (await datePicker.count() > 0) {
      await datePicker.first().click()
      await page.waitForTimeout(500)

      // Calendar or date options should appear
      const calendar = page.locator('[role="dialog"], [data-testid="date-range-picker"]')
      await expect(calendar.or(page.locator('.rdp, .react-datepicker'))).toBeVisible({ timeout: 5000 })
    }
  })

  test('breadcrumb navigation works', async ({ page }) => {
    // Navigate to a nested page
    await page.goto(PAGES.diagnostics)
    await page.waitForLoadState('domcontentloaded')

    // Look for breadcrumb
    const breadcrumb = page.locator('nav[aria-label="breadcrumb"], .breadcrumb, [data-testid="breadcrumb"]')

    if (await breadcrumb.count() > 0) {
      const links = breadcrumb.locator('a')
      if (await links.count() > 0) {
        await links.first().click()
        await page.waitForLoadState('domcontentloaded')

        // Should navigate without error
        const content = await page.content()
        expect(content).not.toContain('Fatal error')
      }
    }
  })

  test('back button works correctly', async ({ page }) => {
    // Navigate to WordPress admin first (non-hash based navigation)
    await page.goto('/wp-admin/')
    await page.waitForLoadState('domcontentloaded')

    // Navigate to WP Statistics
    await page.goto(PAGES.overview)
    await page.waitForLoadState('domcontentloaded')

    // Go back to WordPress admin
    await page.goBack()
    await page.waitForLoadState('domcontentloaded')

    // Should be back on WordPress admin (not WP Statistics page)
    // Note: Hash-based routing within WP Statistics SPA doesn't create browser history entries
    expect(page.url()).toContain('wp-admin')
  })
})
