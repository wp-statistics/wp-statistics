import { test, expect } from '@playwright/test'
import { loginToWordPress, navigateToPage, waitForAppLoad } from './helpers'

test.describe('Diagnostics Page', () => {
  test.beforeEach(async ({ page }) => {
    await loginToWordPress(page)
    await navigateToPage(page, 'diagnostics')
    await waitForAppLoad(page)
  })

  test('diagnostics page loads', async ({ page }) => {
    // Check the page loads without errors
    const content = await page.content()
    expect(content).not.toContain('Fatal error')
    expect(content).not.toContain('Parse error')
  })

  test('diagnostic checks are visible', async ({ page }) => {
    // Look for diagnostic check items or results
    await page.waitForSelector('#wp-statistics-app, .wps-wrap', { timeout: 30000 })

    // The diagnostics page should show some check results
    const pageContent = await page.textContent('body')

    // Should contain some diagnostic-related text
    expect(
      pageContent?.includes('Diagnostic') ||
      pageContent?.includes('Check') ||
      pageContent?.includes('Status') ||
      pageContent?.includes('Health')
    ).toBeTruthy()
  })

  test('can run diagnostic checks', async ({ page }) => {
    // Look for a "Run" or "Check" button
    const runButton = page.locator('button:has-text("Run"), button:has-text("Check"), button:has-text("Test")')
    const buttonCount = await runButton.count()

    if (buttonCount > 0) {
      // Click the first run button
      await runButton.first().click()

      // Wait for some response
      await page.waitForTimeout(3000)

      // Page should still be functional
      const content = await page.content()
      expect(content).not.toContain('Fatal error')
    }
  })
})
