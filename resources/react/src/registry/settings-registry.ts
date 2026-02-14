import * as React from 'react'

import type { SettingsCardConfig, SettingsConfig, SettingsFieldConfig, SettingsTabConfig } from '@/services/settings-config'
import { fetchSettingsConfig } from '@/services/settings-config'

// ── Module-level cache ───────────────────────────────────────────────

let configCache: SettingsConfig | null = null
let configPromise: Promise<SettingsConfig> | null = null

/**
 * Fetch config once; subsequent calls return the cached value.
 * On first call, passes initialTab so the server includes that tab's settings
 * in the response (avoids a second AJAX request).
 */
async function loadConfig(initialTab?: string): Promise<SettingsConfig> {
  if (configCache) return configCache

  if (!configPromise) {
    configPromise = fetchSettingsConfig(initialTab).then((data) => {
      configCache = data
      return data
    })
  }

  return configPromise
}

// ── React hook ───────────────────────────────────────────────────────

interface UseSettingsConfigReturn {
  config: SettingsConfig | null
  isLoading: boolean
  error: string | null
}

/**
 * Fetches the settings config via AJAX on first call, caches in memory.
 * Accepts an optional initialTab to prefetch that tab's settings in the same request.
 */
export function useSettingsConfig(initialTab?: string): UseSettingsConfigReturn {
  const [config, setConfig] = React.useState<SettingsConfig | null>(configCache)
  const [isLoading, setIsLoading] = React.useState(!configCache)
  const [error, setError] = React.useState<string | null>(null)

  React.useEffect(() => {
    if (configCache) {
      setConfig(configCache)
      setIsLoading(false)
      return
    }

    let cancelled = false

    loadConfig(initialTab)
      .then((data) => {
        if (!cancelled) {
          setConfig(data)
          setIsLoading(false)
        }
      })
      .catch((err) => {
        if (!cancelled) {
          setError(err instanceof Error ? err.message : 'Failed to load settings config')
          setIsLoading(false)
        }
      })

    return () => {
      cancelled = true
    }
  }, [initialTab])

  return { config, isLoading, error }
}

// ── Config helpers ───────────────────────────────────────────────────

export interface SettingsTabEntry extends SettingsTabConfig {
  id: string
}

/**
 * Get tabs for an area, sorted by order.
 */
export function getSettingsTabs(config: SettingsConfig, area: 'settings' | 'tools'): SettingsTabEntry[] {
  return Object.entries(config.tabs)
    .filter(([, tab]) => tab.area === area)
    .map(([id, tab]) => ({ ...tab, id }))
    .sort((a, b) => a.order - b.order)
}

/**
 * Get cards for a tab, sorted by order.
 */
export function getSettingsCards(
  config: SettingsConfig,
  tabId: string
): (SettingsCardConfig & { id: string })[] {
  const cards = config.cards[tabId]
  if (!cards) return []

  return Object.entries(cards)
    .map(([id, card]) => ({ ...card, id }))
    .sort((a, b) => a.order - b.order)
}

/**
 * Get fields for a tab+card, sorted by order.
 */
export function getSettingsFields(
  config: SettingsConfig,
  tabId: string,
  cardId: string
): (SettingsFieldConfig & { id: string })[] {
  const fields = config.fields[`${tabId}/${cardId}`]
  if (!fields) return []

  return Object.entries(fields)
    .map(([id, field]) => ({ ...field, id }))
    .sort((a, b) => a.order - b.order)
}

/**
 * Get select options for a specific field by setting_key.
 * Used by component-based tabs to read options from PHP config
 * instead of hardcoding them in React.
 */
export function getFieldOptions(
  config: SettingsConfig,
  tabId: string,
  cardId: string,
  settingKey: string
): { value: string; label: string }[] {
  const fields = config.fields[`${tabId}/${cardId}`]
  if (!fields) return []

  for (const field of Object.values(fields)) {
    if (field.setting_key === settingKey && field.options) {
      return field.options
    }
  }

  return []
}

// ── Component registry ───────────────────────────────────────────────

const componentRegistry = new Map<string, React.ComponentType<Record<string, unknown>>>()

/**
 * Register a React component for use in settings/tools config.
 * Used for component-based tabs, cards, or fields.
 */
export function registerSettingsComponent(
  name: string,
  component: React.ComponentType<Record<string, unknown>>
): void {
  componentRegistry.set(name, component)
}

/**
 * Look up a registered component by name.
 */
export function getSettingsComponent(
  name: string
): React.ComponentType<Record<string, unknown>> | undefined {
  return componentRegistry.get(name)
}
