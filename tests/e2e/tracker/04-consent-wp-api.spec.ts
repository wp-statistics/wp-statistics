import { test, expect } from '@playwright/test'
import {
  setConsentPlugin,
  setBypassAdBlockers,
  waitForHitRequest,
  grantWpConsentApiConsent,
} from '../tracker-helpers'

test.describe('Consent: WP Consent API (via Complianz)', () => {
  test.beforeAll(() => {
    setConsentPlugin('wp_consent_api')
    setBypassAdBlockers(false)
  })

  test.afterAll(() => {
    setConsentPlugin('none')
  })

  test('before consent: no hit sent', async ({ page }) => {
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

  test('wp_has_consent function exists on page', async ({ page }) => {
    await page.goto('/')
    await page.waitForLoadState('domcontentloaded')
    await page.waitForTimeout(1000)

    const hasConsentFn = await page.evaluate(
      () => typeof (window as any).wp_has_consent
    )
    expect(hasConsentFn).toBe('function')
  })

  test('grant statistics consent → hit with tracking_level=full', async ({ page }) => {
    await page.goto('/')
    await page.waitForLoadState('domcontentloaded')
    await page.waitForTimeout(1000)

    const hitPromise = waitForHitRequest(page, 10000)
    await grantWpConsentApiConsent(page, 'statistics')
    const hit = await hitPromise

    expect(hit.params.get('tracking_level')).toBe('full')
  })

  test('grant statistics-anonymous consent → hit with tracking_level=anonymous', async ({ page }) => {
    await page.goto('/')
    await page.waitForLoadState('domcontentloaded')
    await page.waitForTimeout(1000)

    const hitPromise = waitForHitRequest(page, 10000)
    await grantWpConsentApiConsent(page, 'statistics-anonymous')
    const hit = await hitPromise

    expect(hit.params.get('tracking_level')).toBe('anonymous')
  })

  test('consent change event fires consentChanged action', async ({ page }) => {
    await page.goto('/')
    await page.waitForLoadState('domcontentloaded')
    await page.waitForTimeout(1000)

    // Hit should only come after consent
    const hitPromise = waitForHitRequest(page, 10000)

    // Dispatch consent change
    await page.evaluate(() => {
      ;(window as any).wp_has_consent = function (type: string) {
        return type === 'statistics'
      }
      const event = new CustomEvent('wp_listen_for_consent_change', {
        detail: { statistics: 'allow' },
      })
      document.dispatchEvent(event)
    })

    const hit = await hitPromise
    expect(hit.params.get('tracking_level')).toBe('full')
  })
})
