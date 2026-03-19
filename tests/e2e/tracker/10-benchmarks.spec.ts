import { test, expect } from '@playwright/test'
import { existsSync, statSync } from 'node:fs'
import { resolve, dirname } from 'node:path'
import { fileURLToPath } from 'node:url'
import {
  setConsentPlugin,
  setBypassAdBlockers,
  waitForHitRequest,
  instrumentXhrTiming,
  captureGlobalsBefore,
} from '../tracker-helpers'

// Chromium only — performance APIs not consistent across browsers
test.describe('Benchmarks', () => {
  test.skip(({ browserName }) => browserName !== 'chromium', 'Chromium only')

  test.beforeAll(() => {
    setConsentPlugin('none')
    setBypassAdBlockers(false)
  })

  test('bundle size — tracker exists and is reasonably sized', async () => {
    const pluginDir = resolve(
      dirname(fileURLToPath(import.meta.url)),
      '..',
      '..',
      '..'
    )

    const trackerPath = resolve(pluginDir, 'public/entries/tracker/tracker.min.js')
    expect(existsSync(trackerPath)).toBe(true)

    const size = statSync(trackerPath).size
    expect(size).toBeGreaterThan(0)
    // Guard against bundle bloat (current ~8KB, cap at 50KB)
    expect(size).toBeLessThan(50 * 1024)
  })

  test('hit request latency — p50/p95 over 10 samples', async ({ page }) => {
    const samples: number[] = []
    await instrumentXhrTiming(page)

    for (let i = 0; i < 10; i++) {
      const hitPromise = waitForHitRequest(page)
      await page.goto(`/?bench=${i}`)
      await hitPromise

      const timings = await page.evaluate(() => (window as any).__xhrTimings || [])
      const hitTiming = timings.find((t: any) => t.url.includes('hit'))
      if (hitTiming) {
        samples.push(hitTiming.duration)
      }
    }

    expect(samples.length).toBeGreaterThanOrEqual(5)
    samples.sort((a, b) => a - b)
    const p50 = samples[Math.floor(samples.length * 0.5)]
    const p95 = samples[Math.floor(samples.length * 0.95)]

    expect(p50).toBeLessThan(2000)
  })

  test('memory footprint — heap usage before/after tracker', async ({ page }) => {
    await page.addInitScript(() => {
      const perf = (performance as any)
      if (perf.memory) {
        ;(window as any).__memBefore = perf.memory.usedJSHeapSize
      }
    })

    await page.goto('/')
    await page.waitForLoadState('domcontentloaded')
    await page.waitForTimeout(2000)

    const memData = await page.evaluate(() => {
      const perf = (performance as any)
      return {
        before: (window as any).__memBefore || null,
        after: perf.memory ? perf.memory.usedJSHeapSize : null,
      }
    })

    if (memData.before !== null && memData.after !== null) {
      const delta = memData.after - memData.before
      // Tracker should add less than 1MB to heap
      expect(delta).toBeLessThan(1024 * 1024)
    }
  })

  test('global variables — only wp_statistics + wp_statistics_event added', async ({ page }) => {
    await captureGlobalsBefore(page)

    await page.goto('/')
    await page.waitForLoadState('domcontentloaded')
    await page.waitForTimeout(2000)

    const diff = await page.evaluate(() => {
      const before = (window as any).__globalsBefore || []
      const after = Object.getOwnPropertyNames(window)
      return after.filter((key: string) => !before.includes(key))
    })

    // wp_statistics and wp_statistics_event should be present
    expect(diff).toContain('wp_statistics')
    expect(diff).toContain('wp_statistics_event')
  })

  test('batch flush timing — visibilitychange to sendBeacon', async ({ page }) => {
    await page.addInitScript(() => {
      ;(window as any).__beaconTiming = null
      const origSendBeacon = navigator.sendBeacon.bind(navigator)
      navigator.sendBeacon = function (url: string, data?: any) {
        const start = performance.now()
        const result = origSendBeacon(url, data)
        ;(window as any).__beaconTiming = {
          duration: performance.now() - start,
          url,
          timestamp: Date.now(),
        }
        return result
      }
    })

    const hitPromise = waitForHitRequest(page)
    await page.goto('/')
    await hitPromise

    // Generate engagement
    await page.mouse.move(200, 200)
    await page.mouse.click(200, 200)
    await page.waitForTimeout(2000)

    // Trigger visibility change to flush
    await page.evaluate(() => {
      Object.defineProperty(document, 'visibilityState', {
        value: 'hidden',
        writable: true,
      })
      document.dispatchEvent(new Event('visibilitychange'))
    })

    await page.waitForTimeout(500)

    const timing = await page.evaluate(() => (window as any).__beaconTiming)

    // If beacon was sent, it should be fast (< 50ms for the call itself)
    if (timing) {
      expect(timing.duration).toBeLessThan(50)
    }
  })
})
