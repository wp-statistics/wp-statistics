import { test, expect } from '@playwright/test'
import {
  setConsentPlugin,
  setBypassAdBlockers,
  setHybridMode,
  setEventTracking,
  waitForHitRequest,
  waitForBatchRequest,
  getTrackerConfig,
  getLatestSession,
} from '../tracker-helpers'

test.describe('Batch Tracking', () => {
  test.beforeAll(() => {
    setConsentPlugin('none')
    setBypassAdBlockers(false)
    setHybridMode(false)
    setEventTracking(true)
  })

  test.afterAll(() => {
    setHybridMode(false)
    setBypassAdBlockers(false)
    setEventTracking(false)
  })

  // ── Config tests ──────────────────────────────────────────────

  test.describe('batchEndpoint config', () => {
    test('AJAX mode — config has relative batch endpoint', async ({ page }) => {
      setBypassAdBlockers(true)
      setHybridMode(false)

      await page.goto('/')
      await page.waitForLoadState('domcontentloaded')

      const config = await getTrackerConfig(page)
      expect(config).toBeTruthy()
      expect(config.trackingMethod).toBe('ajax')
      expect(config.baseUrls.ajax).toBeTruthy()
      expect(config.batchEndpoint).toContain('wp_statistics_batch')

      // Both endpoints are relative paths
      expect(config.hitEndpoint).toMatch(/^\?action=/)
      expect(config.batchEndpoint).toMatch(/^\?action=/)

      setBypassAdBlockers(false)
    })

    test('AJAX mode (default) — batchEndpoint uses AJAX base', async ({ page }) => {
      setBypassAdBlockers(false)
      setHybridMode(false)

      await page.goto('/')
      await page.waitForLoadState('domcontentloaded')

      const config = await getTrackerConfig(page)
      expect(config).toBeTruthy()

      // Default mode is AJAX — batch uses ajax base
      expect(config.trackingMethod).toBe('ajax')
      expect(config.batchEndpoint).toContain('wp_statistics_batch')
    })

    test('Hybrid Mode — hitEndpoint uses mu-plugin, batchEndpoint uses REST', async ({
      page,
    }) => {
      setHybridMode(true)

      await page.goto('/')
      await page.waitForLoadState('domcontentloaded')

      const config = await getTrackerConfig(page)
      expect(config).toBeTruthy()

      expect(config.trackingMethod).toBe('hybrid')

      // Hit goes to Hybrid Mode mu-plugin
      expect(config.hitEndpoint).toContain('wp-statistics-tracker.php')

      // Batch is a relative REST path — resolved via baseUrls.rest
      expect(config.batchEndpoint).toBe('/batch')
      expect(config.baseUrls.rest).toBeTruthy()

      setHybridMode(false)
    })
  })

  // ── Batch flush tests ─────────────────────────────────────────

  test.describe('batch flush', () => {
    test('batch fires on visibility hidden with engagement time', async ({ page }) => {
      const hitPromise = waitForHitRequest(page)
      await page.goto('/')
      await hitPromise

      // Generate engagement: click to focus + wait
      await page.mouse.click(100, 100)
      await page.waitForTimeout(2000)

      const batchPromise = waitForBatchRequest(page, 5000)
      await page.evaluate(() => {
        Object.defineProperty(document, 'visibilityState', {
          value: 'hidden',
          writable: true,
        })
        document.dispatchEvent(new Event('visibilitychange'))
      })
      const batch = await batchPromise

      expect(batch.payload).toBeTruthy()
      expect(batch.payload.engagement_time).toBeGreaterThan(0)
    })

    test('batch sends incremental deltas (resets after flush)', async ({ page }) => {
      const hitPromise = waitForHitRequest(page)
      await page.goto('/')
      await hitPromise

      // First engagement period
      await page.mouse.click(100, 100)
      await page.waitForTimeout(2000)

      // First flush
      let batchPromise = waitForBatchRequest(page, 5000)
      await page.evaluate(() => {
        Object.defineProperty(document, 'visibilityState', {
          value: 'hidden',
          writable: true,
        })
        document.dispatchEvent(new Event('visibilitychange'))
      })
      const batch1 = await batchPromise
      const time1 = batch1.payload.engagement_time

      expect(time1).toBeGreaterThan(0)

      // "Return" to visible and accumulate more engagement
      await page.evaluate(() => {
        Object.defineProperty(document, 'visibilityState', {
          value: 'visible',
          writable: true,
        })
        document.dispatchEvent(new Event('visibilitychange'))
      })
      await page.mouse.click(200, 200)
      await page.waitForTimeout(2000)

      // Second flush
      batchPromise = waitForBatchRequest(page, 5000)
      await page.evaluate(() => {
        Object.defineProperty(document, 'visibilityState', {
          value: 'hidden',
          writable: true,
        })
        document.dispatchEvent(new Event('visibilitychange'))
      })
      const batch2 = await batchPromise
      const time2 = batch2.payload.engagement_time

      // Second flush should also have engagement > 0 (it's a delta, not cumulative)
      expect(time2).toBeGreaterThan(0)

      // Neither flush should carry the other's time — deltas should be roughly
      // equal to the wait time, not growing cumulatively
      expect(time1).toBeLessThan(10000)
      expect(time2).toBeLessThan(10000)
    })
  })

  // ── Hybrid Mode batch tests ───────────────────────────────

  test.describe('Hybrid Mode', () => {
    test.beforeAll(() => {
      setHybridMode(true)
    })

    test.afterAll(() => {
      setHybridMode(false)
    })

    test('hit goes through Hybrid Mode, batch goes through REST', async ({ page }) => {
      const hitPromise = waitForHitRequest(page)
      await page.goto('/')
      const hit = await hitPromise

      // Hit uses Hybrid Mode mu-plugin
      expect(hit.url).toContain('wp-statistics-tracker.php')

      // Generate engagement
      await page.mouse.click(100, 100)
      await page.waitForTimeout(1500)

      // Flush batch
      const batchPromise = waitForBatchRequest(page, 5000)
      await page.evaluate(() => {
        Object.defineProperty(document, 'visibilityState', {
          value: 'hidden',
          writable: true,
        })
        document.dispatchEvent(new Event('visibilitychange'))
      })
      const batch = await batchPromise

      // Batch uses REST, NOT Hybrid Mode mu-plugin
      expect(batch.url).toContain('wp-statistics/v2/batch')
      expect(batch.url).not.toContain('wp-statistics-tracker.php')

      expect(batch.payload.engagement_time).toBeGreaterThan(0)
    })

    test('custom events work in Hybrid Mode', async ({ page }) => {
      const hitPromise = waitForHitRequest(page)
      await page.goto('/')
      await hitPromise

      // Queue a custom event
      await page.evaluate(() => {
        ;(window as any).wp_statistics.addEvent('custom_event', {
          event_name: 'directfile_test_event',
          event_data: JSON.stringify({ mode: 'hybrid' }),
        })
      })

      // Flush
      const batchPromise = waitForBatchRequest(page, 5000)
      await page.evaluate(() => {
        Object.defineProperty(document, 'visibilityState', {
          value: 'hidden',
          writable: true,
        })
        document.dispatchEvent(new Event('visibilitychange'))
      })
      const batch = await batchPromise

      const event = batch.payload.events?.find(
        (e: any) => e.type === 'custom_event' && e.data?.event_name === 'directfile_test_event'
      )
      expect(event).toBeTruthy()
      expect(event.data.event_data).toContain('hybrid')
    })

    test('batch response is successful in Hybrid Mode', async ({ page }) => {
      const hitPromise = waitForHitRequest(page)
      await page.goto('/')
      await hitPromise

      await page.mouse.click(100, 100)
      await page.waitForTimeout(1000)

      // Intercept batch response to verify server processing
      const responsePromise = page.waitForResponse(
        (resp) =>
          resp.url().includes('wp-statistics/v2/batch') &&
          resp.request().method() === 'POST',
        { timeout: 10000 }
      )

      await page.evaluate(() => {
        Object.defineProperty(document, 'visibilityState', {
          value: 'hidden',
          writable: true,
        })
        document.dispatchEvent(new Event('visibilitychange'))
      })

      const response = await responsePromise
      const body = await response.json()

      expect(body.status).toBe(true)
    })
  })

  // ── Duration accumulation (DB verification) ───────────────────

  test.describe('duration accumulation', () => {
    test('session duration increases across multiple flushes', async ({ page }) => {
      // Record initial hit to create a session
      const hitPromise = waitForHitRequest(page)
      await page.goto('/')
      await hitPromise

      // Get the session created by this hit
      const session1 = getLatestSession()
      if (!session1) {
        test.skip()
        return
      }
      const sessionId = parseInt(session1.ID, 10)
      const initialDuration = parseInt(session1.duration || '0', 10)

      // First engagement period
      await page.mouse.click(100, 100)
      await page.waitForTimeout(3000)

      // First flush
      let batchPromise = waitForBatchRequest(page, 5000)
      await page.evaluate(() => {
        Object.defineProperty(document, 'visibilityState', {
          value: 'hidden',
          writable: true,
        })
        document.dispatchEvent(new Event('visibilitychange'))
      })
      await batchPromise

      // Wait for server to process
      await page.waitForTimeout(500)

      // Check DB after first flush
      const session2 = getLatestSession()
      const durationAfterFirst = parseInt(session2?.duration || '0', 10)
      expect(durationAfterFirst).toBeGreaterThan(initialDuration)

      // Second engagement period
      await page.evaluate(() => {
        Object.defineProperty(document, 'visibilityState', {
          value: 'visible',
          writable: true,
        })
        document.dispatchEvent(new Event('visibilitychange'))
      })
      await page.mouse.click(200, 200)
      await page.waitForTimeout(3000)

      // Second flush
      batchPromise = waitForBatchRequest(page, 5000)
      await page.evaluate(() => {
        Object.defineProperty(document, 'visibilityState', {
          value: 'hidden',
          writable: true,
        })
        document.dispatchEvent(new Event('visibilitychange'))
      })
      await batchPromise

      await page.waitForTimeout(500)

      // Check DB after second flush — duration should have increased further
      const session3 = getLatestSession()
      const durationAfterSecond = parseInt(session3?.duration || '0', 10)
      expect(durationAfterSecond).toBeGreaterThan(durationAfterFirst)
    })

    test('session duration is not overwritten by page navigation', async ({ page }) => {
      // First page — create session and accumulate engagement
      const hitPromise = waitForHitRequest(page)
      await page.goto('/')
      await hitPromise

      await page.mouse.click(100, 100)
      await page.waitForTimeout(2000)

      // Flush engagement before navigating
      let batchPromise = waitForBatchRequest(page, 5000)
      await page.evaluate(() => {
        Object.defineProperty(document, 'visibilityState', {
          value: 'hidden',
          writable: true,
        })
        document.dispatchEvent(new Event('visibilitychange'))
      })
      await batchPromise
      await page.waitForTimeout(500)

      // Record duration after first flush
      const sessionBeforeNav = getLatestSession()
      const durationBeforeNav = parseInt(sessionBeforeNav?.duration || '0', 10)
      expect(durationBeforeNav).toBeGreaterThan(0)

      // Navigate to second page (triggers hit, which calls update)
      const hit2Promise = waitForHitRequest(page)
      await page.goto('/sample-page/')
      await hit2Promise

      await page.waitForTimeout(500)

      // Duration should NOT be reset by update
      const sessionAfterNav = getLatestSession()
      const durationAfterNav = parseInt(sessionAfterNav?.duration || '0', 10)
      expect(durationAfterNav).toBeGreaterThanOrEqual(durationBeforeNav)
    })
  })
})
