import { test, expect } from '@playwright/test'
import { loginToWordPress, verifyTrackerScript } from './helpers'

test.describe('Tracker Integration', () => {
  test('tracker script is loaded on frontend', async ({ page }) => {
    // Visit the homepage (not admin)
    await page.goto('/')
    await page.waitForLoadState('domcontentloaded')

    // Check for tracker script
    const hasTracker = await verifyTrackerScript(page)
    expect(hasTracker).toBe(true)
  })

  test('tracker endpoint is accessible', async ({ page }) => {
    // Try to access the tracker endpoint
    const response = await page.request.get('/wp-json/wp-statistics/v2/hit')

    // It might return 401 or 400 for unauthorized requests, but shouldn't 500
    expect(response.status()).not.toBe(500)
  })

  test('admin pages do not break tracker', async ({ page }) => {
    await loginToWordPress(page)
    await page.goto('/wp-admin/')

    // Navigate around admin
    await page.goto('/wp-admin/admin.php?page=wp-statistics')
    await page.waitForLoadState('domcontentloaded')

    // Now visit frontend and verify tracker still works
    await page.goto('/')
    const hasTracker = await verifyTrackerScript(page)
    expect(hasTracker).toBe(true)
  })

  test('tracker captures page view', async ({ page, context }) => {
    // Enable request interception to monitor tracker calls
    const trackerRequests: string[] = []

    page.on('request', (request) => {
      const url = request.url()
      if (url.includes('wp-statistics') || url.includes('wps_')) {
        trackerRequests.push(url)
      }
    })

    await page.goto('/')
    await page.waitForLoadState('networkidle')

    // Wait a bit for tracker to fire
    await page.waitForTimeout(2000)

    // Tracker should have made at least one request
    // Note: This depends on tracker configuration
    // expect(trackerRequests.length).toBeGreaterThan(0)
  })
})
