import { test, expect } from '@playwright/test'
import {
  setConsentPlugin,
  setBypassAdBlockers,
  waitForHitRequest,
  mockRcbConsentApi,
  overrideConsentMode,
} from '../tracker-helpers'

test.describe('Consent: Real Cookie Banner PRO', () => {
  test.beforeAll(() => {
    // Don't activate RCB plugin (has PHP activation error on this env).
    // Instead, set consent option + mock consentApi client-side.
    setConsentPlugin('none')
    setBypassAdBlockers(false)
  })

  test('before consent: no hit sent', async ({ page }) => {
    // Override consent mode to RCB + mock consentApi returning no consent
    await overrideConsentMode(page, 'real_cookie_banner')
    await page.addInitScript(() => {
      ;(window as any).consentApi = {
        consentSync: () => ({ cookie: null, cookieOptIn: false }),
        consent: () => new Promise((_r, reject) => setTimeout(() => reject(), 100)),
      }
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
  })

  test('data processing consent → tracking_level=full', async ({ page }) => {
    await overrideConsentMode(page, 'real_cookie_banner')
    await mockRcbConsentApi(page, 'data-processing')

    const hitPromise = waitForHitRequest(page, 10000)
    await page.goto('/')
    const hit = await hitPromise

    expect(hit.params.get('tracking_level')).toBe('full')
  })

  test('base consent only → tracking_level=anonymous', async ({ page }) => {
    await overrideConsentMode(page, 'real_cookie_banner')
    await mockRcbConsentApi(page, 'base')

    const hitPromise = waitForHitRequest(page, 10000)
    await page.goto('/')
    const hit = await hitPromise

    expect(hit.params.get('tracking_level')).toBe('anonymous')
  })

  test('async consent resolves → consentChanged fires and hit sends', async ({ page }) => {
    await overrideConsentMode(page, 'real_cookie_banner')
    // Mock consentApi: sync returns nothing, async resolves for wp-statistics
    await page.addInitScript(() => {
      ;(window as any).consentApi = {
        consentSync: () => ({ cookie: null, cookieOptIn: false }),
        consent: function (name: string) {
          if (name === 'wp-statistics') {
            return new Promise((resolve) => setTimeout(resolve, 500))
          }
          return new Promise((_r, reject) => setTimeout(() => reject(), 100))
        },
      }
    })

    const hitPromise = waitForHitRequest(page, 10000)
    await page.goto('/')
    const hit = await hitPromise

    expect(hit.params.get('tracking_level')).toBe('anonymous')
  })
})
