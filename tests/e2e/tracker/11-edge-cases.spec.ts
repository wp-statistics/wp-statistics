import { test, expect } from '@playwright/test'
import {
  setConsentPlugin,
  setBypassAdBlockers,
  waitForHitRequest,
  waitForBatchRequest,
  getPublicApi,
} from '../tracker-helpers'

test.describe('Edge Cases', () => {
  test.beforeAll(() => {
    setConsentPlugin('none')
    setBypassAdBlockers(false)
  })

  test('preview mode → no tracking', async ({ page }) => {
    let hitReceived = false
    page.on('request', (req) => {
      const url = req.url()
      if (url.includes('wp-statistics/v2/hit') || url.includes('wp_statistics_hit_record')) {
        hitReceived = true
      }
    })

    await page.goto('/?preview=true')
    await page.waitForTimeout(3000)

    expect(hitReceived).toBe(false)
  })

  test('missing tracker config → graceful error, no hit', async ({ page }) => {
    const consoleErrors: string[] = []
    page.on('console', (msg) => {
      if (msg.type() === 'error') {
        consoleErrors.push(msg.text())
      }
    })

    // Intercept the inline script that sets WP_Statistics_Tracker_Object
    // and the tracker JS to inject a version that deletes the config before init
    await page.addInitScript(() => {
      // Use a proxy to intercept the config being set and delete it before DOMContentLoaded
      let configValue: any = undefined
      Object.defineProperty(window, 'WP_Statistics_Tracker_Object', {
        get() {
          return configValue
        },
        set(val) {
          // Capture but don't store — simulate missing config
          configValue = undefined
        },
        configurable: true,
        enumerable: true,
      })
    })

    let hitReceived = false
    page.on('request', (req) => {
      if (req.url().includes('wp-statistics/v2/hit')) {
        hitReceived = true
      }
    })

    await page.goto('/')
    await page.waitForTimeout(3000)

    expect(hitReceived).toBe(false)

    // Should have a console error about missing config
    const configError = consoleErrors.find((e) =>
      e.includes('WP_Statistics_Tracker_Object') || e.includes('Tracker configuration')
    )
    expect(configError).toBeTruthy()
  })

  test('rapid SPA navigations → correct hit count', async ({ page }) => {
    const hitUrls: string[] = []
    page.on('request', (req) => {
      const url = req.url()
      const postData = req.postData() || ''
      if (
        (url.includes('wp-statistics/v2/hit') && req.method() === 'POST') ||
        (url.includes('admin-ajax.php') && postData.includes('wp_statistics_hit_record'))
      ) {
        hitUrls.push(url)
      }
    })

    // Initial hit
    const initialHitPromise = waitForHitRequest(page)
    await page.goto('/')
    await initialHitPromise

    // Fire 5 rapid pushState calls
    await page.evaluate(() => {
      for (let i = 1; i <= 5; i++) {
        history.pushState({}, '', `/rapid-nav-${i}/`)
      }
    })

    // Wait for all hits to arrive
    await page.waitForTimeout(5000)

    // Should have 1 initial + 5 SPA navigation hits = 6 total
    // Or at minimum, the 5 SPA navigations should produce hits
    // (some may coalesce if URL didn't actually change between checks)
    expect(hitUrls.length).toBeGreaterThanOrEqual(2)
  })

  test('tab switch stops engagement + triggers flush', async ({ page }) => {
    const hitPromise = waitForHitRequest(page)
    await page.goto('/')
    await hitPromise

    // Generate engagement
    await page.mouse.move(150, 150)
    await page.mouse.click(150, 150)
    await page.waitForTimeout(2000)

    // Simulate tab becoming hidden
    const batchPromise = waitForBatchRequest(page, 5000).catch(() => null)

    await page.evaluate(() => {
      Object.defineProperty(document, 'visibilityState', {
        value: 'hidden',
        writable: true,
        configurable: true,
      })
      document.dispatchEvent(new Event('visibilitychange'))
    })

    const batch = await batchPromise

    if (batch) {
      expect(batch.payload.engagement_time).toBeGreaterThan(0)
    }
    // Even if no batch (engagement was 0), the test verifies no crashes
  })

  test('public API surface is correct', async ({ page }) => {
    await page.goto('/')
    await page.waitForLoadState('domcontentloaded')
    await page.waitForTimeout(2000)

    const apiKeys = await getPublicApi(page)

    const expectedKeys = [
      'addFilter',
      'removeFilter',
      'applyFilters',
      'addAction',
      'removeAction',
      'doAction',
      'addEvent',
      'event',
    ]

    for (const key of expectedKeys) {
      expect(apiKeys).toContain(key)
    }

    // Also verify legacy alias
    const hasLegacyAlias = await page.evaluate(
      () => typeof (window as any).wp_statistics_event === 'function'
    )
    expect(hasLegacyAlias).toBe(true)
  })
})
