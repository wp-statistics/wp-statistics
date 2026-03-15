import { test, expect } from '@playwright/test'
import {
  setConsentPlugin,
  setBypassAdBlockers,
  waitForHitRequest,
  waitForBatchRequest,
} from '../tracker-helpers'

test.describe('Core Tracking', () => {
  test.beforeAll(() => {
    setConsentPlugin('none')
    setBypassAdBlockers(false)
  })

  test('hit fires on page load', async ({ page }) => {
    const hitPromise = waitForHitRequest(page)
    await page.goto('/')
    const hit = await hitPromise

    expect(hit.params.get('resourceUriId')).toBeTruthy()
    expect(hit.params.get('resource_type')).toBeTruthy()
    expect(hit.params.get('tracking_level')).toBeTruthy()
    expect(hit.params.get('timezone')).toBeTruthy()
    expect(hit.params.get('language')).toBeTruthy()
    expect(hit.params.get('languageFullName')).toBeTruthy()
    expect(hit.params.get('screenWidth')).toBeTruthy()
    expect(hit.params.get('screenHeight')).toBeTruthy()
    expect(hit.params.get('page_uri')).toBeTruthy()
    expect(hit.params.get('referred')).not.toBeNull()
  })

  test('tracking_level=full with no consent plugin', async ({ page }) => {
    const hitPromise = waitForHitRequest(page)
    await page.goto('/')
    const hit = await hitPromise

    expect(hit.params.get('tracking_level')).toBe('full')
  })

  test('hit response is valid JSON with status:true', async ({ page }) => {
    const hitPromise = waitForHitRequest(page)
    await page.goto('/')
    const hit = await hitPromise

    expect(hit.response).toBeTruthy()
    expect(hit.response.status).toBe(true)
  })

  test('engagement initializes after hit', async ({ page }) => {
    const hitPromise = waitForHitRequest(page)
    await page.goto('/')
    await hitPromise

    await page.mouse.move(100, 100)
    await page.mouse.click(100, 100)
    await page.waitForTimeout(1500)

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

  test('batch flushes on page exit with queued event', async ({ page }) => {
    const hitPromise = waitForHitRequest(page)
    await page.goto('/')
    await hitPromise

    await page.evaluate(() => {
      ;(window as any).wp_statistics.addEvent('custom_event', {
        event_name: 'test_event',
        event_data: JSON.stringify({ key: 'val' }),
      })
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

    expect(batch.payload).toBeTruthy()
    expect(batch.payload.events).toBeInstanceOf(Array)
    expect(batch.payload.events.length).toBeGreaterThanOrEqual(1)

    const event = batch.payload.events.find((e: any) => e.type === 'custom_event')
    expect(event).toBeTruthy()
    expect(event.data.event_name).toBe('test_event')
  })

  test('wp_statistics.addEvent() queues events', async ({ page }) => {
    const hitPromise = waitForHitRequest(page)
    await page.goto('/')
    await hitPromise

    await page.evaluate(() => {
      ;(window as any).wp_statistics.addEvent('custom_event', {
        event_name: 'add_event_test',
        event_data: JSON.stringify({ key: 'val' }),
      })
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
      (e: any) => e.type === 'custom_event' && e.data?.event_name === 'add_event_test'
    )
    expect(event).toBeTruthy()
  })

  test('window.wp_statistics_event() backward compat', async ({ page }) => {
    const hitPromise = waitForHitRequest(page)
    await page.goto('/')
    await hitPromise

    await page.evaluate(() => {
      ;(window as any).wp_statistics_event('legacy_test', { foo: 'bar' })
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
      (e: any) => e.type === 'custom_event' && e.data?.event_name === 'legacy_test'
    )
    expect(event).toBeTruthy()
  })
})
