import { test, expect } from '@playwright/test'
import {
  setConsentPlugin,
  setBypassAdBlockers,
  waitForHitRequest,
  waitForBatchRequest,
} from '../tracker-helpers'

test.describe('Hook/Filter System', () => {
  test.beforeAll(() => {
    setConsentPlugin('none')
    setBypassAdBlockers(false)
  })

  test('addFilter(trackingLevel) modifies tracking level', async ({ page }) => {
    await page.addInitScript(() => {
      document.addEventListener('DOMContentLoaded', () => {
        const api = (window as any).wp_statistics
        if (api) {
          api.addFilter('trackingLevel', () => 'anonymous', 5)
        }
      })
    })

    const hitPromise = waitForHitRequest(page, 10000)
    await page.goto('/')
    const hit = await hitPromise

    expect(hit.params.get('tracking_level')).toBe('anonymous')
  })

  test('addFilter(hitData) modifies payload', async ({ page }) => {
    await page.addInitScript(() => {
      document.addEventListener('DOMContentLoaded', () => {
        const api = (window as any).wp_statistics
        if (api) {
          api.addFilter('hitData', (data: any) => {
            data.custom_test_field = 'hook_test_value'
            return data
          })
        }
      })
    })

    const hitPromise = waitForHitRequest(page, 10000)
    await page.goto('/')
    const hit = await hitPromise

    expect(hit.params.get('custom_test_field')).toBe('hook_test_value')
  })

  test('addAction(trackerInit) fires after tracker ready', async ({ page }) => {
    await page.addInitScript(() => {
      ;(window as any).__trackerInitFired = false
      document.addEventListener('DOMContentLoaded', () => {
        const api = (window as any).wp_statistics
        if (api) {
          api.addAction('trackerInit', () => {
            ;(window as any).__trackerInitFired = true
          })
        }
      })
    })

    await page.goto('/')
    await page.waitForLoadState('domcontentloaded')

    // Wait for tracker init to complete
    await page.waitForFunction(
      () => (window as any).__trackerInitFired === true,
      { timeout: 10000 }
    )

    const fired = await page.evaluate(() => (window as any).__trackerInitFired)
    expect(fired).toBe(true)
  })

  test('addAction(afterHit) fires with response data', async ({ page }) => {
    await page.addInitScript(() => {
      ;(window as any).__afterHitData = null
      document.addEventListener('DOMContentLoaded', () => {
        const api = (window as any).wp_statistics
        if (api) {
          api.addAction('afterHit', (response: any, success: boolean) => {
            ;(window as any).__afterHitData = { response, success }
          })
        }
      })
    })

    await page.goto('/')
    await page.waitForFunction(
      () => (window as any).__afterHitData !== null,
      { timeout: 10000 }
    )

    const data = await page.evaluate(() => (window as any).__afterHitData)
    expect(data).toBeTruthy()
    expect(data.success).toBe(true)
    expect(data.response).toBeTruthy()
    expect(data.response.status).toBe(true)
  })

  test('addAction(beforeFlush) fires before batch request', async ({ page }) => {
    await page.addInitScript(() => {
      ;(window as any).__beforeFlushFired = false
      ;(window as any).__beforeFlushReason = null
      document.addEventListener('DOMContentLoaded', () => {
        const api = (window as any).wp_statistics
        if (api) {
          api.addAction('beforeFlush', (_payload: any, reason: string) => {
            ;(window as any).__beforeFlushFired = true
            ;(window as any).__beforeFlushReason = reason
          })
        }
      })
    })

    const hitPromise = waitForHitRequest(page)
    await page.goto('/')
    await hitPromise

    // Generate engagement
    await page.mouse.move(200, 200)
    await page.mouse.click(200, 200)
    await page.waitForTimeout(1500)

    // Trigger flush via visibilitychange (instead of navigation, which
    // conflicts with the batch route interceptor)
    await page.evaluate(() => {
      Object.defineProperty(document, 'visibilityState', {
        value: 'hidden',
        writable: true,
      })
      document.dispatchEvent(new Event('visibilitychange'))
    })

    await page.waitForTimeout(500)

    const fired = await page.evaluate(() => (window as any).__beforeFlushFired)
    expect(fired).toBe(true)

    const reason = await page.evaluate(() => (window as any).__beforeFlushReason)
    expect(reason).toBe('visibility_hidden')
  })
})
