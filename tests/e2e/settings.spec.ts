import { test, expect } from '@playwright/test'
import { loginToWordPress, navigateToPage, waitForAppLoad } from './helpers'

test.describe('Settings Page', () => {
  test.beforeEach(async ({ page }) => {
    await loginToWordPress(page)
    await navigateToPage(page, 'settings')
    await waitForAppLoad(page)
  })

  test('settings page loads without errors', async ({ page }) => {
    // Check for settings content
    const content = await page.content()
    expect(content).not.toContain('Fatal error')
    expect(content).not.toContain('Parse error')
  })

  test('settings tabs are visible', async ({ page }) => {
    // Look for settings navigation or tabs
    const settingsContainer = page.locator('#wp-statistics-app, .wps-wrap')
    await expect(settingsContainer).toBeVisible()
  })

  test('can navigate to different settings sections', async ({ page }) => {
    // Navigate to tracking settings
    await page.goto('/wp-admin/admin.php?page=wp-statistics#/settings/tracking')
    await page.waitForLoadState('domcontentloaded')

    // Navigate to privacy settings
    await page.goto('/wp-admin/admin.php?page=wp-statistics#/settings/privacy')
    await page.waitForLoadState('domcontentloaded')

    // Navigate to advanced settings
    await page.goto('/wp-admin/admin.php?page=wp-statistics#/settings/advanced')
    await page.waitForLoadState('domcontentloaded')
  })

  test('settings form elements are interactive', async ({ page }) => {
    // Check for form elements (switches, inputs, selects)
    const switches = page.locator('button[role="switch"], input[type="checkbox"]')
    const count = await switches.count()

    // There should be at least some toggleable settings
    expect(count).toBeGreaterThan(0)
  })
})
