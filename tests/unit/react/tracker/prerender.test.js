import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest'

/**
 * Tests for the prerender defer guard in core/tracker.js init().
 *
 * Verifies that:
 *  1. When document.prerendering is false/undefined, init() proceeds and fires a hit.
 *  2. When document.prerendering is true, init() registers a prerenderingchange
 *     listener and does NOT fire a hit.
 *  3. When prerenderingchange fires (page activated), init() re-enters and fires the hit.
 *  4. If the prerender is discarded (event never fires), no hit is sent.
 */

const hitSend = vi.fn(() => Promise.resolve(true))
const navigationInit = vi.fn()
const engagementInit = vi.fn()
const queueInit = vi.fn()
const queueStartPeriodicFlush = vi.fn()
const registerConsentAdapter = vi.fn()
const isPreviewMock = vi.fn(() => false)
const getConfigMock = vi.fn(() => ({ option: { record_exclusions: false } }))
const getTrackingLevelsMock = vi.fn(() => ({ none: 'none', full: 'full' }))
const applyFiltersMock = vi.fn((_name, value) => value)
const addActionMock = vi.fn()
const doActionMock = vi.fn()

vi.mock('../../../../resources/entries/tracker/core/hooks.js', () => ({
  applyFilters: (name, value) => applyFiltersMock(name, value),
  addAction: (...args) => addActionMock(...args),
  doAction: (...args) => doActionMock(...args),
  addFilter: vi.fn(),
  removeFilter: vi.fn(),
  removeAction: vi.fn(),
}))

vi.mock('../../../../resources/entries/tracker/core/config.js', () => ({
  getConfig: () => getConfigMock(),
  isPreview: () => isPreviewMock(),
  getTrackingLevels: () => getTrackingLevelsMock(),
  getResource: vi.fn(() => ''),
}))

vi.mock('../../../../resources/entries/tracker/core/consent.js', () => ({
  registerConsentAdapter: () => registerConsentAdapter(),
}))

vi.mock('../../../../resources/entries/tracker/trackers/hit.js', () => ({
  send: (level) => hitSend(level),
}))

vi.mock('../../../../resources/entries/tracker/trackers/engagement.js', () => ({
  init: () => engagementInit(),
}))

vi.mock('../../../../resources/entries/tracker/transport/queue.js', () => ({
  init: (opts) => queueInit(opts),
  startPeriodicFlush: () => queueStartPeriodicFlush(),
  add: vi.fn(),
}))

vi.mock('../../../../resources/entries/tracker/spa/navigation.js', () => ({
  init: (cb) => navigationInit(cb),
}))

const setPrerendering = (value) => {
  Object.defineProperty(document, 'prerendering', {
    value,
    configurable: true,
    writable: true,
  })
}

// Capture the prerenderingchange listener from a spy and invoke it directly.
// Real dispatchEvent would trigger leaked listeners from previous tests in the
// same file (jsdom shares `document` across tests), giving false-positive
// duplicate-hit failures.
const captureListener = (spy) => {
  const call = spy.mock.calls.find(([event]) => event === 'prerenderingchange')
  if (!call) throw new Error('No prerenderingchange listener was registered')
  return call[1]
}

let init

beforeEach(async () => {
  vi.resetAllMocks()
  hitSend.mockResolvedValue(true)
  isPreviewMock.mockReturnValue(false)
  getConfigMock.mockReturnValue({ option: { record_exclusions: false } })
  getTrackingLevelsMock.mockReturnValue({ none: 'none', full: 'full' })
  applyFiltersMock.mockImplementation((_name, value) => value)

  // Reset module state — tracker keeps `hasInitialized` in module scope.
  vi.resetModules()
  ;({ init } = await import('../../../../resources/entries/tracker/core/tracker.js'))

  delete document.prerendering
})

afterEach(() => {
  delete document.prerendering
})

describe('tracker init() — prerender defer', () => {
  it('fires hit normally when document.prerendering is undefined (non-Chromium)', async () => {
    init()
    await Promise.resolve() // flush microtasks

    expect(hitSend).toHaveBeenCalledTimes(1)
    expect(hitSend).toHaveBeenCalledWith('full')
  })

  it('fires hit normally when document.prerendering is false', async () => {
    setPrerendering(false)

    init()
    await Promise.resolve()

    expect(hitSend).toHaveBeenCalledTimes(1)
  })

  it('does NOT fire hit while document.prerendering is true', async () => {
    setPrerendering(true)

    init()
    await Promise.resolve()

    expect(hitSend).not.toHaveBeenCalled()
    expect(registerConsentAdapter).not.toHaveBeenCalled()
  })

  it('registers exactly one prerenderingchange listener while prerendering', () => {
    setPrerendering(true)
    const addSpy = vi.spyOn(document, 'addEventListener')

    init()

    const prerenderListenerCalls = addSpy.mock.calls.filter(
      ([event]) => event === 'prerenderingchange'
    )
    expect(prerenderListenerCalls).toHaveLength(1)
    expect(prerenderListenerCalls[0][2]).toEqual({ once: true })
  })

  it('fires hit on prerenderingchange activation', async () => {
    setPrerendering(true)
    const addSpy = vi.spyOn(document, 'addEventListener')

    init()
    expect(hitSend).not.toHaveBeenCalled()

    const listener = captureListener(addSpy)
    setPrerendering(false)
    listener()
    await Promise.resolve()

    expect(hitSend).toHaveBeenCalledTimes(1)
    expect(hitSend).toHaveBeenCalledWith('full')
  })

  it('never fires hit when prerender is discarded (listener never invoked)', async () => {
    setPrerendering(true)

    init()
    await Promise.resolve()

    // Simulate discard: the document is GC'd, no activation event ever fires.
    expect(hitSend).not.toHaveBeenCalled()
  })

  it('listener is registered with { once: true } so the browser auto-removes it after firing', () => {
    setPrerendering(true)
    const addSpy = vi.spyOn(document, 'addEventListener')

    init()

    const call = addSpy.mock.calls.find(([event]) => event === 'prerenderingchange')
    expect(call[2]).toEqual({ once: true })
  })

  it('after activation, init proceeds through normal pipeline', async () => {
    setPrerendering(true)
    const addSpy = vi.spyOn(document, 'addEventListener')

    init()
    const listener = captureListener(addSpy)
    setPrerendering(false)
    listener()
    await Promise.resolve()
    await Promise.resolve() // hit.send() resolves on second tick

    expect(registerConsentAdapter).toHaveBeenCalledTimes(1)
    expect(navigationInit).toHaveBeenCalledTimes(1)
    expect(doActionMock).toHaveBeenCalledWith('trackerInit')
  })
})
