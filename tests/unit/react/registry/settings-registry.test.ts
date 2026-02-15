import { renderHook, waitFor } from '@testing-library/react'
import { beforeEach, describe, expect, it, vi } from 'vitest'

import type { SettingsConfig } from '@/services/settings-config'

// Mock the settings-config service
vi.mock('@/services/settings-config', () => ({
  fetchSettingsConfig: vi.fn(),
}))

import { fetchSettingsConfig } from '@/services/settings-config'

const mockFetchSettingsConfig = vi.mocked(fetchSettingsConfig)

/**
 * Build a minimal SettingsConfig fixture.
 */
function makeConfig(overrides?: Partial<SettingsConfig>): SettingsConfig {
  return {
    tabs: {
      general: { area: 'settings', label: 'General', icon: 'settings', order: 10 },
      display: { area: 'settings', label: 'Display', icon: 'monitor', order: 20 },
      privacy: { area: 'settings', label: 'Privacy', icon: 'shield', order: 30 },
      'system-info': { area: 'tools', label: 'System Info', icon: 'info', order: 10 },
      diagnostics: { area: 'tools', label: 'Diagnostics', icon: 'stethoscope', order: 20 },
    },
    cards: {
      general: {
        tracking: { title: 'Tracking', order: 10 },
        'tracker-config': { title: 'Tracker Config', order: 20 },
      },
      display: {
        'admin-interface': { title: 'Admin Interface', order: 10 },
      },
    },
    fields: {
      'general/tracking': {
        visitors_log: { type: 'toggle', setting_key: 'visitors_log', order: 10 },
      },
      'general/tracker-config': {
        bypass: { type: 'toggle', setting_key: 'bypass_ad_blockers', order: 10 },
      },
      'display/admin-interface': {
        disable_editor: { type: 'toggle', setting_key: 'disable_editor', order: 10 },
        disable_column: { type: 'toggle', setting_key: 'disable_column', order: 20 },
      },
    },
    ...overrides,
  }
}

// ── Pure helper function tests ───────────────────────────────────────

describe('getSettingsTabs', () => {
  // Import dynamically after mock setup to avoid cache issues
  let getSettingsTabs: typeof import('@/registry/settings-registry').getSettingsTabs

  beforeEach(async () => {
    const mod = await import('@/registry/settings-registry')
    getSettingsTabs = mod.getSettingsTabs
  })

  it('filters by area and sorts by order', () => {
    const config = makeConfig()
    const settingsTabs = getSettingsTabs(config, 'settings')

    expect(settingsTabs).toHaveLength(3)
    expect(settingsTabs[0].id).toBe('general')
    expect(settingsTabs[1].id).toBe('display')
    expect(settingsTabs[2].id).toBe('privacy')
  })

  it('returns tools tabs sorted by order', () => {
    const config = makeConfig()
    const toolsTabs = getSettingsTabs(config, 'tools')

    expect(toolsTabs).toHaveLength(2)
    expect(toolsTabs[0].id).toBe('system-info')
    expect(toolsTabs[1].id).toBe('diagnostics')
  })

  it('returns empty for area with no tabs', () => {
    const config = makeConfig({ tabs: {} })
    expect(getSettingsTabs(config, 'settings')).toEqual([])
  })
})

describe('getSettingsCards', () => {
  let getSettingsCards: typeof import('@/registry/settings-registry').getSettingsCards

  beforeEach(async () => {
    const mod = await import('@/registry/settings-registry')
    getSettingsCards = mod.getSettingsCards
  })

  it('returns sorted cards for tab', () => {
    const config = makeConfig()
    const cards = getSettingsCards(config, 'general')

    expect(cards).toHaveLength(2)
    expect(cards[0].id).toBe('tracking')
    expect(cards[1].id).toBe('tracker-config')
  })

  it('returns empty for unknown tab', () => {
    const config = makeConfig()
    expect(getSettingsCards(config, 'nonexistent')).toEqual([])
  })
})

describe('getSettingsFields', () => {
  let getSettingsFields: typeof import('@/registry/settings-registry').getSettingsFields

  beforeEach(async () => {
    const mod = await import('@/registry/settings-registry')
    getSettingsFields = mod.getSettingsFields
  })

  it('returns sorted fields for tab/card path', () => {
    const config = makeConfig()
    const fields = getSettingsFields(config, 'display', 'admin-interface')

    expect(fields).toHaveLength(2)
    expect(fields[0].id).toBe('disable_editor')
    expect(fields[1].id).toBe('disable_column')
  })

  it('returns empty for unknown path', () => {
    const config = makeConfig()
    expect(getSettingsFields(config, 'general', 'nonexistent')).toEqual([])
  })
})

// ── Component registry tests ─────────────────────────────────────────

describe('component registry', () => {
  let registerSettingsComponent: typeof import('@/registry/settings-registry').registerSettingsComponent
  let getSettingsComponent: typeof import('@/registry/settings-registry').getSettingsComponent

  beforeEach(async () => {
    const mod = await import('@/registry/settings-registry')
    registerSettingsComponent = mod.registerSettingsComponent
    getSettingsComponent = mod.getSettingsComponent
  })

  it('stores and retrieves a component', () => {
    const MockComponent = () => null
    registerSettingsComponent('TestComponent', MockComponent)

    expect(getSettingsComponent('TestComponent')).toBe(MockComponent)
  })

  it('returns undefined for unknown component', () => {
    expect(getSettingsComponent('NonExistentComponent')).toBeUndefined()
  })
})

// ── useSettingsConfig hook tests ─────────────────────────────────────

describe('useSettingsConfig', () => {
  beforeEach(async () => {
    vi.clearAllMocks()

    // Reset the module-level cache by re-importing with a fresh module
    vi.resetModules()
  })

  it('returns loading state initially', async () => {
    const config = makeConfig()
    mockFetchSettingsConfig.mockResolvedValue(config)

    const { useSettingsConfig } = await import('@/registry/settings-registry')
    const { result } = renderHook(() => useSettingsConfig())

    expect(result.current.isLoading).toBe(true)
    expect(result.current.config).toBeNull()
    expect(result.current.error).toBeNull()

    await waitFor(() => {
      expect(result.current.isLoading).toBe(false)
    })
  })

  it('returns config after fetch resolves', async () => {
    const config = makeConfig()
    mockFetchSettingsConfig.mockResolvedValue(config)

    const { useSettingsConfig } = await import('@/registry/settings-registry')
    const { result } = renderHook(() => useSettingsConfig())

    await waitFor(() => {
      expect(result.current.isLoading).toBe(false)
    })

    expect(result.current.config).toEqual(config)
    expect(result.current.error).toBeNull()
  })

  it('returns error on fetch failure', async () => {
    mockFetchSettingsConfig.mockRejectedValue(new Error('Network error'))

    const { useSettingsConfig } = await import('@/registry/settings-registry')
    const { result } = renderHook(() => useSettingsConfig())

    await waitFor(() => {
      expect(result.current.isLoading).toBe(false)
    })

    expect(result.current.error).toBe('Network error')
    expect(result.current.config).toBeNull()
  })

  it('returns generic error for non-Error rejection', async () => {
    mockFetchSettingsConfig.mockRejectedValue('something went wrong')

    const { useSettingsConfig } = await import('@/registry/settings-registry')
    const { result } = renderHook(() => useSettingsConfig())

    await waitFor(() => {
      expect(result.current.isLoading).toBe(false)
    })

    expect(result.current.error).toBe('Failed to load settings config')
  })

  it('caches config across multiple hook instances', async () => {
    const config = makeConfig()
    mockFetchSettingsConfig.mockResolvedValue(config)

    const { useSettingsConfig } = await import('@/registry/settings-registry')

    const { result: result1 } = renderHook(() => useSettingsConfig())

    await waitFor(() => {
      expect(result1.current.isLoading).toBe(false)
    })

    // Second hook should use cached data
    const { result: result2 } = renderHook(() => useSettingsConfig())

    // Should already have config from cache (no loading)
    expect(result2.current.config).toEqual(config)

    // fetch should only have been called once
    expect(mockFetchSettingsConfig).toHaveBeenCalledTimes(1)
  })
})
