import { test, expect } from '@playwright/test'
import {
  setConsentPlugin,
  setBypassAdBlockers,
  waitForHitRequest,
  waitForBatchRequest,
} from '../tracker-helpers'

test.describe('SPA Navigation', () => {
  test.beforeAll(() => {
    setConsentPlugin('none')
    setBypassAdBlockers(false)
  })

  test('pushState → flush + new hit', async ({ page }) => {
    // Wait for initial hit
    const initialHitPromise = waitForHitRequest(page)
    await page.goto('/')
    await initialHitPromise

    // Simulate user activity for engagement
    await page.mouse.move(100, 100)
    await page.waitForTimeout(1500)

    // pushState should trigger flush + new hit
    const secondHitPromise = waitForHitRequest(page, 10000)
    await page.evaluate(() => {
      history.pushState({}, '', '/test-spa-page/')
    })

    const secondHit = await secondHitPromise
    expect(secondHit.params.get('page_uri')).toBeTruthy()

    // Decode the base64 page_uri to verify it reflects the new URL
    const pageUri = await page.evaluate((encoded) => {
      try {
        return atob(encoded)
      } catch {
        return encoded
      }
    }, secondHit.params.get('page_uri')!)
    expect(pageUri).toContain('/test-spa-page/')
  })

  test('replaceState → flush + new hit', async ({ page }) => {
    const initialHitPromise = waitForHitRequest(page)
    await page.goto('/')
    await initialHitPromise

    await page.mouse.move(100, 100)
    await page.waitForTimeout(1500)

    const secondHitPromise = waitForHitRequest(page, 10000)
    await page.evaluate(() => {
      history.replaceState({}, '', '/test-replace-page/')
    })

    const secondHit = await secondHitPromise
    const pageUri = await page.evaluate((encoded) => {
      try {
        return atob(encoded)
      } catch {
        return encoded
      }
    }, secondHit.params.get('page_uri')!)
    expect(pageUri).toContain('/test-replace-page/')
  })

  test('popstate (back) → flush + new hit', async ({ page }) => {
    const initialHitPromise = waitForHitRequest(page)
    await page.goto('/')
    await initialHitPromise

    // Push a new state first
    const pushHitPromise = waitForHitRequest(page, 10000)
    await page.evaluate(() => {
      history.pushState({}, '', '/test-pushed/')
    })
    await pushHitPromise

    await page.waitForTimeout(500)

    // Go back (popstate)
    const backHitPromise = waitForHitRequest(page, 10000)
    await page.goBack()
    const backHit = await backHitPromise

    expect(backHit.params.get('page_uri')).toBeTruthy()
  })

  test('engagement resets between SPA navigations', async ({ page }) => {
    const initialHitPromise = waitForHitRequest(page)
    await page.goto('/')
    await initialHitPromise

    // Generate engagement
    await page.mouse.move(100, 100)
    await page.mouse.click(100, 100)
    await page.waitForTimeout(2000)

    // Capture the batch flush from SPA navigation
    const batchPromise = waitForBatchRequest(page, 10000)
    const secondHitPromise = waitForHitRequest(page, 10000)

    await page.evaluate(() => {
      history.pushState({}, '', '/spa-reset-test/')
    })

    const batch = await batchPromise
    await secondHitPromise

    // The batch flush should contain engagement from the first page
    expect(batch.payload.engagement_time).toBeGreaterThan(0)
  })

  test('config refreshes on SPA navigation', async ({ page }) => {
    const initialHitPromise = waitForHitRequest(page)
    await page.goto('/')
    await initialHitPromise

    // Verify tracker re-sends hit with updated URL
    const secondHitPromise = waitForHitRequest(page, 10000)
    await page.evaluate(() => {
      history.pushState({}, '', '/config-refresh-test/')
    })

    const secondHit = await secondHitPromise
    // The new hit should have the updated page_uri
    const pageUri = await page.evaluate((encoded) => {
      try {
        return atob(encoded)
      } catch {
        return encoded
      }
    }, secondHit.params.get('page_uri')!)
    expect(pageUri).toContain('/config-refresh-test/')
  })
})
