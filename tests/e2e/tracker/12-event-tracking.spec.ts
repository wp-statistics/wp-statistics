import { test, expect } from '@playwright/test'
import {
  setConsentPlugin,
  setBypassAdBlockers,
  setEventTracking,
  waitForHitRequest,
  waitForBatchRequest,
} from '../tracker-helpers'

test.describe('Event Tracking', () => {
  test.beforeAll(() => {
    setConsentPlugin('none')
    setBypassAdBlockers(false)
    setEventTracking(true)
  })

  test.afterAll(() => {
    setEventTracking(false)
  })

  test('batch endpoint processes event via FormData correctly', async ({ page }) => {
    const hitPromise = waitForHitRequest(page)
    await page.goto('/')
    await hitPromise

    // Queue a custom event
    await page.evaluate(() => {
      ;(window as any).wp_statistics.addEvent('custom_event', {
        event_name: 'batch_test',
        event_data: JSON.stringify({ source: 'e2e' }),
      })
    })

    // Intercept the batch response to verify server-side processing
    const responsePromise = page.waitForResponse(
      (resp) =>
        (resp.url().includes('wp-statistics/v2/batch') ||
          resp.url().includes('admin-ajax.php')) &&
        resp.request().method() === 'POST',
      { timeout: 10000 }
    )

    // Trigger batch flush
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
    expect(body.processed).toBeGreaterThanOrEqual(1)
  })

  test('addEvent is gated when event tracking is disabled', async ({ page }) => {
    setEventTracking(false)

    const hitPromise = waitForHitRequest(page)
    await page.goto('/')
    await hitPromise

    // Try to queue an event — should be silently dropped
    await page.evaluate(() => {
      ;(window as any).wp_statistics.addEvent('custom_event', {
        event_name: 'should_not_queue',
        event_data: '{}',
      })
    })

    // Trigger batch flush (engagement time may still flush, but no events should be in it)
    await page.mouse.move(100, 100)
    await page.waitForTimeout(500)

    const batchPromise = waitForBatchRequest(page, 5000)
    await page.evaluate(() => {
      Object.defineProperty(document, 'visibilityState', {
        value: 'hidden',
        writable: true,
      })
      document.dispatchEvent(new Event('visibilitychange'))
    })
    const batch = await batchPromise

    // Engagement flush may happen, but events array should be empty
    const events = batch.payload.events || []
    const customEvent = events.find(
      (e: any) => e.type === 'custom_event' && e.data?.event_name === 'should_not_queue'
    )
    expect(customEvent).toBeUndefined()

    // Re-enable for subsequent tests
    setEventTracking(true)
  })

  test('wp_statistics.event() backward compat works with event tracking enabled', async ({
    page,
  }) => {
    const hitPromise = waitForHitRequest(page)
    await page.goto('/')
    await hitPromise

    await page.evaluate(() => {
      ;(window as any).wp_statistics_event('compat_test', { key: 'value' })
    })

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
      (e: any) => e.type === 'custom_event' && e.data?.event_name === 'compat_test'
    )
    expect(event).toBeTruthy()
  })
})
