import { test, expect } from '@playwright/test'
import {
  setConsentPlugin,
  setBypassAdBlockers,
  waitForHitRequest,
  updateWpStatisticsOption,
  overrideConsentMode,
} from '../tracker-helpers'

test.describe('Consent: Borlabs Cookie', () => {
  test.beforeAll(() => {
    // Borlabs blocks the tracker script server-side when consent isn't given.
    // We test two scenarios:
    // 1. When Borlabs blocks (real plugin activation, no consent) → no tracker
    // 2. When Borlabs allows (client-side mock) → tracker uses anonymousTracking option
    setConsentPlugin('none')
    setBypassAdBlockers(false)
  })

  test.afterAll(() => {
    setConsentPlugin('none')
    updateWpStatisticsOption('anonymous_tracking', false)
  })

  test('without Borlabs consent: no tracking (script blocked)', async ({ page }) => {
    // Activate Borlabs — it will block the tracker script unless user consented
    setConsentPlugin('borlabs_cookie')

    let hitReceived = false
    page.on('request', (req) => {
      const url = req.url()
      if (url.includes('wp-statistics/v2/hit') || url.includes('wp_statistics_collect')) {
        hitReceived = true
      }
    })

    await page.goto('/')
    await page.waitForTimeout(3000)

    // No hit should fire — Borlabs blocks the tracker script server-side
    expect(hitReceived).toBe(false)

    // Cleanup: back to none so tracker loads for next tests
    setConsentPlugin('none')
  })

  test('with Borlabs consent + anonymous_tracking=false → tracking_level=full', async ({ page }) => {
    // Simulate Borlabs allowing the script by overriding consent mode client-side
    updateWpStatisticsOption('anonymous_tracking', false)
    await overrideConsentMode(page, 'borlabs_cookie')

    const hitPromise = waitForHitRequest(page, 10000)
    await page.goto('/')
    const hit = await hitPromise

    expect(hit.params.get('tracking_level')).toBe('full')
  })

  test('with Borlabs consent + anonymous_tracking=true → tracking_level=anonymous', async ({ page }) => {
    updateWpStatisticsOption('anonymous_tracking', true)
    await overrideConsentMode(page, 'borlabs_cookie')

    const hitPromise = waitForHitRequest(page, 10000)
    await page.goto('/')
    const hit = await hitPromise

    expect(hit.params.get('tracking_level')).toBe('anonymous')

    // Cleanup
    updateWpStatisticsOption('anonymous_tracking', false)
  })
})
