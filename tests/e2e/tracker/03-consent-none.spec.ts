import { test, expect } from '@playwright/test'
import { setConsentPlugin, setBypassAdBlockers, waitForHitRequest } from '../tracker-helpers'

test.describe('Consent: None (no consent plugin)', () => {
  test.beforeAll(() => {
    setConsentPlugin('none')
    setBypassAdBlockers(false)
  })

  test('tracks immediately on page load', async ({ page }) => {
    const hitPromise = waitForHitRequest(page, 5000)
    await page.goto('/')
    const hit = await hitPromise

    expect(hit.url).toBeTruthy()
  })

  test('tracking_level=full', async ({ page }) => {
    const hitPromise = waitForHitRequest(page)
    await page.goto('/')
    const hit = await hitPromise

    expect(hit.params.get('tracking_level')).toBe('full')
  })

  test('no consent warnings in console', async ({ page }) => {
    const warnings: string[] = []
    page.on('console', (msg) => {
      if (msg.type() === 'warning' || msg.type() === 'error') {
        const text = msg.text()
        if (text.includes('consent') || text.includes('wp_has_consent')) {
          warnings.push(text)
        }
      }
    })

    const hitPromise = waitForHitRequest(page)
    await page.goto('/')
    await hitPromise
    await page.waitForTimeout(1000)

    expect(warnings).toHaveLength(0)
  })
})
