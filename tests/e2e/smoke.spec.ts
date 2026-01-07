import { test, expect } from '@playwright/test'
import { loginToWordPress, navigateToPage, waitForAppLoad, PAGES } from './helpers'

test.describe('Smoke Tests', () => {
  test.beforeEach(async ({ page }) => {
    await loginToWordPress(page)
  })

  test('admin dashboard loads', async ({ page }) => {
    await page.goto('/wp-admin/')
    await expect(page).toHaveURL(/\/wp-admin\//)
    await expect(page.locator('#adminmenu')).toBeVisible()
  })

  test('WP Statistics menu is visible', async ({ page }) => {
    await page.goto('/wp-admin/')
    // Check for WP Statistics menu item (use specific ID to avoid matching other plugins)
    const menu = page.locator('#toplevel_page_wp-statistics .wp-menu-name')
    await expect(menu).toBeVisible()
  })

  test('Overview page loads', async ({ page }) => {
    await navigateToPage(page, 'overview')
    await waitForAppLoad(page)
    // Page should have WP Statistics content
    await expect(page.locator('#wp-statistics-app, .wps-wrap')).toBeVisible()
  })

  test('All main pages are accessible', async ({ page }) => {
    const pagesToTest: (keyof typeof PAGES)[] = [
      'overview',
      'visitors',
      'pages',
      'referrals',
      'geographic',
      'devices',
      'settings',
    ]

    for (const pageKey of pagesToTest) {
      await page.goto(PAGES[pageKey])
      await page.waitForLoadState('domcontentloaded')
      // Check page doesn't show PHP error
      const content = await page.content()
      expect(content).not.toContain('Fatal error')
      expect(content).not.toContain('Parse error')
      expect(content).not.toContain('Warning:')
    }
  })
})
