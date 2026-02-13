import * as React from 'react'

import type { SettingsCardConfig, SettingsConfig, SettingsFieldConfig, SettingsTabConfig } from '@/services/settings-config'
import { fetchSettingsConfig } from '@/services/settings-config'

// ── Module-level cache ───────────────────────────────────────────────

let configCache: SettingsConfig | null = null
let configPromise: Promise<SettingsConfig> | null = null

/**
 * Fetch config once; subsequent calls return the cached value.
 */
async function loadConfig(): Promise<SettingsConfig> {
  if (configCache) return configCache

  if (!configPromise) {
    configPromise = fetchSettingsConfig().then((data) => {
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
 */
export function useSettingsConfig(): UseSettingsConfigReturn {
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

    loadConfig()
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
  }, [])

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
