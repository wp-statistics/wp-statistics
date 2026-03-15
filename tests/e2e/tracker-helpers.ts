/**
 * Tracker E2E Test Helpers
 *
 * WP-CLI wrappers, request interceptors, consent mocks, and benchmark instrumentation.
 */

import { execSync } from 'node:child_process'
import { resolve, dirname } from 'node:path'
import { fileURLToPath } from 'node:url'
import { Page, BrowserContext, Request } from '@playwright/test'

const __filename = fileURLToPath(import.meta.url)
const __dirname = dirname(__filename)

// Derive WP path from plugin directory structure: tests/e2e/ -> ../../ -> plugin root
// The WP install is two levels above the plugin: wp-content/plugins/wp-statistics -> wp root
const WP_PATH = resolve(__dirname, '..', '..', '..', '..', '..')

// ---------------------------------------------------------------------------
// WP-CLI
// ---------------------------------------------------------------------------

export function wpCli(command: string): string {
  return execSync(`wp ${command} --path="${WP_PATH}"`, {
    encoding: 'utf-8',
    timeout: 30000,
  }).trim()
}

export function activatePlugin(slug: string): void {
  try {
    wpCli(`plugin activate ${slug}`)
  } catch (error) {
    const msg = error instanceof Error ? error.message : String(error)
    if (msg.includes('already active')) return
    throw new Error(`Failed to activate plugin '${slug}': ${msg}`)
  }
}

export function deactivatePlugin(slug: string): void {
  try {
    wpCli(`plugin deactivate ${slug}`)
  } catch (error) {
    const msg = error instanceof Error ? error.message : String(error)
    if (msg.includes('already deactivated') || msg.includes('not active')) return
    throw new Error(`Failed to deactivate plugin '${slug}': ${msg}`)
  }
}

export function getWpStatisticsOption(key: string): string {
  try {
    return wpCli(`option patch get wp_statistics ${key}`)
  } catch {
    return ''
  }
}

export function updateWpStatisticsOption(key: string, value: string | boolean): void {
  const val = typeof value === 'boolean' ? (value ? '1' : '0') : value
  wpCli(`option patch update wp_statistics ${key} "${val}"`)
}

// ---------------------------------------------------------------------------
// Consent plugin switcher
// ---------------------------------------------------------------------------

const CONSENT_PLUGINS = [
  'wp-consent-api',
  'complianz-gdpr',
  'real-cookie-banner-pro',
  'borlabs-cookie',
]

export function setConsentPlugin(mode: 'none' | 'wp_consent_api' | 'real_cookie_banner' | 'borlabs_cookie'): void {
  // Deactivate all consent plugins
  for (const slug of CONSENT_PLUGINS) {
    deactivatePlugin(slug)
  }

  // Set the consent integration option
  updateWpStatisticsOption('consent_integration', mode === 'none' ? 'none' : mode)

  // Activate relevant plugin(s)
  // Note: RCB tests use client-side mocking (overrideConsentMode + mockRcbConsentApi)
  // instead of activating the plugin, which has a PHP activation error in this env.
  switch (mode) {
    case 'wp_consent_api':
      activatePlugin('wp-consent-api')
      activatePlugin('complianz-gdpr')
      break
    case 'borlabs_cookie':
      activatePlugin('borlabs-cookie')
      break
  }
}

export function setBypassAdBlockers(enabled: boolean): void {
  updateWpStatisticsOption('bypass_ad_blockers', enabled)
}

// ---------------------------------------------------------------------------
// Request interceptors
// ---------------------------------------------------------------------------

export interface CapturedRequest {
  url: string
  method: string
  postData: string | null
  params: URLSearchParams | null
}

export function captureTrackerRequests(page: Page): CapturedRequest[] {
  const captured: CapturedRequest[] = []
  page.on('request', (req: Request) => {
    const url = req.url()
    if (
      url.includes('wp-statistics/v2/hit') ||
      url.includes('wp-statistics/v2/batch') ||
      (url.includes('admin-ajax.php') &&
        (req.postData()?.includes('wp_statistics_hit_record') ||
          req.postData()?.includes('wp_statistics_batch')))
    ) {
      captured.push({
        url,
        method: req.method(),
        postData: req.postData(),
        params: req.postData() ? new URLSearchParams(req.postData()!) : null,
      })
    }
  })
  return captured
}

export async function waitForHitRequest(
  page: Page,
  timeout = 10000
): Promise<{ url: string; postData: string; params: URLSearchParams; response: any }> {
  return new Promise((resolve, reject) => {
    const timer = setTimeout(() => {
      page.removeListener('requestfinished', handler)
      reject(new Error(`Hit request not received within ${timeout}ms`))
    }, timeout)

    const handler = async (req: Request) => {
      const url = req.url()
      const postData = req.postData() || ''
      const isHit =
        url.includes('wp-statistics/v2/hit') ||
        (url.includes('admin-ajax.php') && postData.includes('wp_statistics_hit_record'))

      if (isHit && req.method() === 'POST') {
        clearTimeout(timer)
        page.removeListener('requestfinished', handler)

        let response: any = null
        try {
          const res = await req.response()
          if (res) {
            const text = await res.text()
            try {
              response = JSON.parse(text)
            } catch {
              response = { raw: text }
            }
          }
        } catch {
          // response may not be available
        }

        resolve({
          url,
          postData,
          params: new URLSearchParams(postData),
          response,
        })
      }
    }

    page.on('requestfinished', handler)
  })
}

export async function waitForBatchRequest(
  page: Page,
  timeout = 10000
): Promise<{ url: string; postData: string; payload: any }> {
  return new Promise((resolve, reject) => {
    // Use page.route to intercept batch requests — this captures sendBeacon FormData
    const routePattern = /\/(wp-statistics\/v2\/batch|admin-ajax\.php)/

    const timer = setTimeout(() => {
      cleanup()
      page.unroute(routePattern).catch(() => {})
      reject(new Error(`Batch request not received within ${timeout}ms`))
    }, timeout)

    let resolved = false
    const cleanup = () => {
      if (resolved) return
      resolved = true
      clearTimeout(timer)
    }
    page.route(routePattern, async (route) => {
      const request = route.request()
      const url = request.url()
      const postData = request.postData() || ''

      const isBatch =
        url.includes('wp-statistics/v2/batch') ||
        postData.includes('wp_statistics_batch')

      if (isBatch && !resolved) {
        cleanup()

        let payload: any = null
        try {
          // sendBeacon FormData: multipart boundary encoding
          // Look for batch_data field in multipart or URL-encoded body
          const batchDataMatch = postData.match(
            /(?:name="batch_data"\r?\n\r?\n|batch_data=)(.+?)(?:\r?\n----|&|$)/s
          )
          if (batchDataMatch) {
            payload = JSON.parse(
              batchDataMatch[1].startsWith('%')
                ? decodeURIComponent(batchDataMatch[1])
                : batchDataMatch[1]
            )
          } else {
            payload = JSON.parse(postData)
          }
        } catch {
          payload = { raw: postData }
        }

        // Continue the request so the server processes it
        await route.continue()
        // Unroute after capture
        await page.unroute(routePattern).catch(() => {})

        resolve({ url, postData, payload })
      } else {
        await route.continue()
      }
    })
  })
}

// ---------------------------------------------------------------------------
// Consent simulators
// ---------------------------------------------------------------------------

/**
 * Override wp_has_consent to return true for the given category,
 * then dispatch the consent change event.
 */
export async function grantWpConsentApiConsent(
  page: Page,
  category: 'statistics' | 'statistics-anonymous'
): Promise<void> {
  await page.evaluate((cat) => {
    ;(window as any).wp_has_consent = function (type: string) {
      if (type === cat) return true
      if (cat === 'statistics' && type === 'statistics-anonymous') return true
      return false
    }
    const event = new CustomEvent('wp_listen_for_consent_change', {
      detail: { [cat]: 'allow' },
    })
    document.dispatchEvent(event)
  }, category)
}

/**
 * Mock Real Cookie Banner consentApi before tracker loads.
 */
export function mockRcbConsentApi(
  page: Page,
  level: 'data-processing' | 'base' | 'async-base'
): Promise<void> {
  return page.addInitScript((lvl) => {
    const api: any = {
      consentSync: function (name: string) {
        if (lvl === 'data-processing' && name === 'wp-statistics-with-data-processing') {
          return { cookie: 'test', cookieOptIn: true }
        }
        if ((lvl === 'base' || lvl === 'async-base') && name === 'wp-statistics') {
          return { cookie: 'test', cookieOptIn: true }
        }
        if (lvl === 'data-processing' && name === 'wp-statistics') {
          return { cookie: null, cookieOptIn: false }
        }
        return { cookie: null, cookieOptIn: false }
      },
      consent: function (name: string) {
        if (lvl === 'async-base' && name === 'wp-statistics') {
          return Promise.resolve()
        }
        return new Promise((_resolve, reject) => {
          setTimeout(() => reject(new Error('no consent')), 100)
        })
      },
    }
    ;(window as any).consentApi = api
  }, level)
}

/**
 * Set Borlabs Cookie consent cookie in the browser context.
 */
export async function setBorlabsConsentCookie(context: BrowserContext, consented: boolean): Promise<void> {
  const baseUrl = process.env.WP_BASE_URL || 'https://wp-statistics.test'
  const domain = new URL(baseUrl).hostname
  if (consented) {
    await context.addCookies([
      {
        name: 'borlabs-cookie',
        value: encodeURIComponent(
          JSON.stringify({
            consents: { statistics: ['wp-statistics'] },
            domainPath: domain + '/',
            expires: new Date(Date.now() + 86400000).toISOString(),
            uid: 'test-uid',
            version: 1,
          })
        ),
        domain,
        path: '/',
      },
    ])
  }
}

/**
 * Override the consent mode in WP_Statistics_Tracker_Object before tracker reads it.
 * Used when a consent plugin can't be activated via WP-CLI but we need to test
 * the client-side consent adapter.
 */
export function overrideConsentMode(page: Page, mode: string): Promise<void> {
  return page.addInitScript((m) => {
    // Intercept the tracker config before DOMContentLoaded
    const origDescriptor = Object.getOwnPropertyDescriptor(window, 'WP_Statistics_Tracker_Object')
    let configValue: any = origDescriptor?.value

    Object.defineProperty(window, 'WP_Statistics_Tracker_Object', {
      get() {
        return configValue
      },
      set(val) {
        if (val && val.option) {
          if (!val.option.consent) val.option.consent = {}
          val.option.consent.mode = m
        }
        configValue = val
      },
      configurable: true,
      enumerable: true,
    })

    // If config is already set (inline script), patch it now
    if (configValue && configValue.option) {
      if (!configValue.option.consent) configValue.option.consent = {}
      configValue.option.consent.mode = m
    }
  }, mode)
}

// ---------------------------------------------------------------------------
// Tracker inspection
// ---------------------------------------------------------------------------

export async function getTrackerConfig(page: Page): Promise<any> {
  return page.evaluate(() => (window as any).WP_Statistics_Tracker_Object || null)
}

export async function getPublicApi(page: Page): Promise<string[]> {
  return page.evaluate(() => {
    const api = (window as any).wp_statistics
    return api ? Object.keys(api) : []
  })
}

export function getLatestVisitorRecord(): any {
  try {
    const result = wpCli(
      `eval 'global $wpdb; $row = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}statistics_visitors ORDER BY ID DESC LIMIT 1", ARRAY_A); echo json_encode($row);'`
    )
    return JSON.parse(result)
  } catch {
    return null
  }
}

export function getLatestSessionRecord(): any {
  try {
    const result = wpCli(
      `eval 'global $wpdb; $row = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}statistics_sessions ORDER BY session_id DESC LIMIT 1", ARRAY_A); echo json_encode($row);'`
    )
    return JSON.parse(result)
  } catch {
    return null
  }
}

// ---------------------------------------------------------------------------
// Benchmark instrumentation
// ---------------------------------------------------------------------------

/**
 * Patch XMLHttpRequest to capture request timing for hits.
 */
export function instrumentXhrTiming(page: Page): Promise<void> {
  return page.addInitScript(() => {
    ;(window as any).__xhrTimings = []
    const OrigXhr = window.XMLHttpRequest
    const origOpen = OrigXhr.prototype.open
    const origSend = OrigXhr.prototype.send

    OrigXhr.prototype.open = function (...args: any[]) {
      ;(this as any).__url = args[1]
      return origOpen.apply(this, args as any)
    }

    OrigXhr.prototype.send = function (...args: any[]) {
      const url = (this as any).__url || ''
      if (url.includes('wp-statistics')) {
        const start = performance.now()
        this.addEventListener('loadend', () => {
          ;(window as any).__xhrTimings.push({
            url,
            duration: performance.now() - start,
            status: this.status,
          })
        })
      }
      return origSend.apply(this, args as any)
    }
  })
}

/**
 * Snapshot window property names before tracker loads.
 */
export function captureGlobalsBefore(page: Page): Promise<void> {
  return page.addInitScript(() => {
    ;(window as any).__globalsBefore = Object.getOwnPropertyNames(window)
  })
}
