import { test, expect } from '@playwright/test'
import {
  setConsentPlugin,
  setBypassAdBlockers,
  waitForHitRequest,
  getTrackerConfig,
} from '../tracker-helpers'

test.describe('Delivery Modes', () => {
  test.beforeAll(() => {
    setConsentPlugin('none')
  })

  test.afterAll(() => {
    setBypassAdBlockers(false)
  })

  test('REST API mode — hit and batch URLs use wp-json', async ({ page }) => {
    setBypassAdBlockers(false)

    const hitPromise = waitForHitRequest(page)
    await page.goto('/')
    const hit = await hitPromise

    expect(hit.url).toContain('/wp-json/wp-statistics/v2/hit')
  })

  test('AJAX bypass mode — hit URL uses admin-ajax.php', async ({ page }) => {
    setBypassAdBlockers(true)

    const hitPromise = waitForHitRequest(page)
    await page.goto('/')
    const hit = await hitPromise

    expect(hit.url).toContain('admin-ajax.php')
    expect(hit.params.get('action')).toBe('wp_statistics_collect')

    // Reset
    setBypassAdBlockers(false)
  })

  test('endpoint config has hitEndpoint and batchEndpoint', async ({ page }) => {
    setBypassAdBlockers(false)

    await page.goto('/')
    await page.waitForLoadState('domcontentloaded')

    const config = await getTrackerConfig(page)
    expect(config).toBeTruthy()
    expect(config.baseUrls).toBeTruthy()
    expect(config.trackingMethod).toBeTruthy()
    expect(config.hitEndpoint).toBeTruthy()

    // batchEndpoint is a relative path; base URL resolved via trackingMethod
    expect(config.batchEndpoint).toBeTruthy()
    expect(config.batchEndpoint).toContain('wp_statistics_batch')
  })

})
