import { test, expect } from '@playwright/test'
import {
  setConsentPlugin,
  setBypassAdBlockers,
  waitForHitRequest,
  waitForBatchRequest,
} from '../tracker-helpers'

/**
 * Tests for the premium Event Tracker module (click + file_download capturers)
 * and consent gating of event capture.
 *
 * Requires: event_tracking enabled, premium event-tracker module active.
 */
test.describe('Event Capture', () => {
  test.beforeAll(() => {
    setConsentPlugin('none')
    setBypassAdBlockers(false)
  })

  // ── Helper: set up page with navigation prevention ──────────

  async function setupEventsPage(page: any) {
    const hitPromise = waitForHitRequest(page)
    await page.goto('/events/')
    await hitPromise

    // Prevent actual navigation on link clicks (bubble phase)
    // Event-tracker.js uses capture phase, so it fires first
    await page.evaluate(() => {
      document.querySelectorAll('a').forEach((a: HTMLAnchorElement) => {
        a.removeAttribute('target')
        a.addEventListener('click', (e: Event) => e.preventDefault())
      })
    })
  }

  // ── Click capturer ──────────────────────────────────────────

  test.describe('click capturer', () => {
    test('external link click is captured as click event', async ({ page }) => {
      await setupEventsPage(page)

      // Intercept addEvent to verify capture
      const captured = await page.evaluate(() => {
        return new Promise((resolve) => {
          const orig = (window as any).wp_statistics.addEvent
          ;(window as any).wp_statistics.addEvent = function (type: string, data: any) {
            ;(window as any).wp_statistics.addEvent = orig
            orig.call(this, type, data)
            resolve({ type, data })
          }
          document.querySelector<HTMLAnchorElement>('a[href="https://example.com"]')!.click()
        })
      })

      expect((captured as any).type).toBe('custom_event')
      expect((captured as any).data.event_name).toBe('click')

      const eventData = JSON.parse((captured as any).data.event_data)
      expect(eventData.tu).toContain('example.com')
      expect(eventData.ev).toBeTruthy() // link text
    })

    test('internal link click is NOT captured', async ({ page }) => {
      await setupEventsPage(page)

      // Monitor addEvent — should NOT be called for internal link
      const wasCaptured = await page.evaluate(() => {
        return new Promise((resolve) => {
          let captured = false
          const orig = (window as any).wp_statistics.addEvent
          ;(window as any).wp_statistics.addEvent = function (type: string, data: any) {
            captured = true
            orig.call(this, type, data)
          }
          document.querySelector<HTMLAnchorElement>('a[href="/sample-page/"]')!.click()

          // Wait a tick to ensure no async capture
          setTimeout(() => {
            ;(window as any).wp_statistics.addEvent = orig
            resolve(captured)
          }, 100)
        })
      })

      expect(wasCaptured).toBe(false)
    })

    test('click event data contains expected fields', async ({ page }) => {
      await setupEventsPage(page)

      const eventData = await page.evaluate(() => {
        return new Promise((resolve) => {
          const orig = (window as any).wp_statistics.addEvent
          ;(window as any).wp_statistics.addEvent = function (type: string, data: any) {
            ;(window as any).wp_statistics.addEvent = orig
            orig.call(this, type, data)
            resolve(JSON.parse(data.event_data))
          }
          document.querySelector<HTMLAnchorElement>('a[href="https://example.com"]')!.click()
        })
      })

      // Standard click event fields
      expect(eventData).toHaveProperty('tu')  // target URL
      expect(eventData).toHaveProperty('ev')  // element value (text)
      expect(eventData).toHaveProperty('et')  // event timestamp
      expect(eventData).toHaveProperty('mb')  // mouse button
      expect(eventData).toHaveProperty('eid') // element id
      expect(eventData).toHaveProperty('ec')  // element class
      expect(eventData).toHaveProperty('pid') // page id
    })
  })

  // ── File download capturer ──────────────────────────────────

  test.describe('file_download capturer', () => {
    test('external PDF link is captured as file_download', async ({ page }) => {
      await setupEventsPage(page)

      const captured = await page.evaluate(() => {
        return new Promise((resolve) => {
          const orig = (window as any).wp_statistics.addEvent
          ;(window as any).wp_statistics.addEvent = function (type: string, data: any) {
            ;(window as any).wp_statistics.addEvent = orig
            orig.call(this, type, data)
            resolve({ type, data })
          }
          document.querySelector<HTMLAnchorElement>('a[href*="dummy.pdf"]')!.click()
        })
      })

      expect((captured as any).data.event_name).toBe('file_download')

      const eventData = JSON.parse((captured as any).data.event_data)
      expect(eventData.tu).toContain('dummy.pdf')
      expect(eventData.fn).toBe('dummy')   // filename without extension
      expect(eventData.fx).toBe('pdf')     // file extension
    })

    test('internal ZIP link is captured as file_download', async ({ page }) => {
      await setupEventsPage(page)

      const captured = await page.evaluate(() => {
        return new Promise((resolve) => {
          const orig = (window as any).wp_statistics.addEvent
          ;(window as any).wp_statistics.addEvent = function (type: string, data: any) {
            ;(window as any).wp_statistics.addEvent = orig
            orig.call(this, type, data)
            resolve({ type, data })
          }
          document.querySelector<HTMLAnchorElement>('a[href*="test-file.zip"]')!.click()
        })
      })

      expect((captured as any).data.event_name).toBe('file_download')

      const eventData = JSON.parse((captured as any).data.event_data)
      expect(eventData.fn).toBe('test-file')
      expect(eventData.fx).toBe('zip')
    })

    test('internal PDF link is captured as file_download (not click)', async ({ page }) => {
      await setupEventsPage(page)

      const captured = await page.evaluate(() => {
        return new Promise((resolve) => {
          const orig = (window as any).wp_statistics.addEvent
          ;(window as any).wp_statistics.addEvent = function (type: string, data: any) {
            ;(window as any).wp_statistics.addEvent = orig
            orig.call(this, type, data)
            resolve({ type, data })
          }
          document.querySelector<HTMLAnchorElement>('a[href*="test-file.pdf"]')!.click()
        })
      })

      // Download takes priority over click — even for internal links
      expect((captured as any).data.event_name).toBe('file_download')
      expect((captured as any).data.event_name).not.toBe('click')
    })

    test('download takes priority over click for external PDF', async ({ page }) => {
      await setupEventsPage(page)

      // External PDF could match both click (external) and download (.pdf)
      // Download should take priority
      const captured = await page.evaluate(() => {
        return new Promise((resolve) => {
          const orig = (window as any).wp_statistics.addEvent
          ;(window as any).wp_statistics.addEvent = function (type: string, data: any) {
            ;(window as any).wp_statistics.addEvent = orig
            orig.call(this, type, data)
            resolve({ type, data })
          }
          document.querySelector<HTMLAnchorElement>('a[href*="dummy.pdf"]')!.click()
        })
      })

      expect((captured as any).data.event_name).toBe('file_download')
    })

    test('file_download event data contains filename fields', async ({ page }) => {
      await setupEventsPage(page)

      const eventData = await page.evaluate(() => {
        return new Promise((resolve) => {
          const orig = (window as any).wp_statistics.addEvent
          ;(window as any).wp_statistics.addEvent = function (type: string, data: any) {
            ;(window as any).wp_statistics.addEvent = orig
            orig.call(this, type, data)
            resolve(JSON.parse(data.event_data))
          }
          document.querySelector<HTMLAnchorElement>('a[href*="test-file.zip"]')!.click()
        })
      })

      expect(eventData).toHaveProperty('tu')   // target URL
      expect(eventData).toHaveProperty('fn')   // filename
      expect(eventData).toHaveProperty('fx')   // file extension
      expect(eventData).toHaveProperty('et')   // event timestamp
      expect(eventData).toHaveProperty('wcdl') // WooCommerce download flag
      expect((eventData as any).wcdl).toBe(false)
    })
  })

  // ── Batch delivery ──────────────────────────────────────────

  test.describe('batch delivery', () => {
    test('queued events appear in batch payload on flush', async ({ page }) => {
      await setupEventsPage(page)

      // Click multiple links to queue events
      await page.evaluate(() => {
        document.querySelector<HTMLAnchorElement>('a[href="https://example.com"]')!.click()
        document.querySelector<HTMLAnchorElement>('a[href*="test-file.zip"]')!.click()
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

      const events = batch.payload.events || []
      const clickEvent = events.find(
        (e: any) => e.type === 'custom_event' && e.data?.event_name === 'click'
      )
      const downloadEvent = events.find(
        (e: any) => e.type === 'custom_event' && e.data?.event_name === 'file_download'
      )

      expect(clickEvent).toBeTruthy()
      expect(downloadEvent).toBeTruthy()
    })

    test('batch payload includes engagement_time alongside events', async ({ page }) => {
      await setupEventsPage(page)

      // Generate engagement
      await page.mouse.click(200, 200)
      await page.waitForTimeout(1500)

      // Queue an event
      await page.evaluate(() => {
        document.querySelector<HTMLAnchorElement>('a[href="https://example.com"]')!.click()
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

      // Both engagement and events in same batch
      expect(batch.payload.engagement_time).toBeGreaterThan(0)
      expect(batch.payload.events.length).toBeGreaterThanOrEqual(1)
    })
  })

  // ── Consent gating ──────────────────────────────────────────

  test.describe('consent', () => {
    test('denied consent prevents tracker init — no events captured', async ({ page }) => {
      setConsentPlugin('wp_consent_api')

      const hitPromise = waitForHitRequest(page, 3000).catch(() => null)
      await page.goto('/events/')

      // With wp_consent_api active, tracker waits for consent
      // Don't grant consent — tracker should NOT initialize
      await hitPromise

      // Try to use the event API — should exist but trackerInit should NOT have fired
      const trackerInitFired = await page.evaluate(() => {
        return new Promise((resolve) => {
          // Check if event-tracker bound its listeners by trying to detect
          // if a click would produce an addEvent call
          let captured = false
          const api = (window as any).wp_statistics
          if (!api || !api.addEvent) {
            resolve('no_api')
            return
          }

          const orig = api.addEvent
          api.addEvent = function (type: string, data: any) {
            captured = true
            return orig.call(this, type, data)
          }

          // Simulate an external link click
          const link = document.createElement('a')
          link.href = 'https://external-consent-test.com'
          link.textContent = 'test'
          document.body.appendChild(link)
          link.click()
          document.body.removeChild(link)

          setTimeout(() => {
            api.addEvent = orig
            resolve(captured ? 'event_captured' : 'no_capture')
          }, 200)
        })
      })

      // Without consent, trackerInit never fires, so event-tracker.js
      // never binds its click listener — no events should be captured
      expect(trackerInitFired).toBe('no_capture')

      // Reset
      setConsentPlugin('none')
    })
  })

  // ── Event config ────────────────────────────────────────────

  test.describe('config', () => {
    test('WP_Statistics_Event_Config present with capturers', async ({ page }) => {
      await page.goto('/')
      await page.waitForLoadState('domcontentloaded')

      const eventConfig = await page.evaluate(
        () => (window as any).WP_Statistics_Event_Config
      )

      expect(eventConfig).toBeTruthy()
      expect(eventConfig.capturers).toBeTruthy()
      expect(eventConfig.capturers.click).toBe(true)
      expect(eventConfig.capturers.file_download).toBeTruthy()
      expect(eventConfig.capturers.file_download.fileExtensions).toBeInstanceOf(Array)
      expect(eventConfig.capturers.file_download.fileExtensions).toContain('pdf')
      expect(eventConfig.capturers.file_download.fileExtensions).toContain('zip')
    })

  })
})
