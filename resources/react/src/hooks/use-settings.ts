import * as React from 'react'

import { getTabSettings, saveTabSettings, type SettingsTab } from '@/services/settings'
import { settingsCache } from '@/services/settings-config'

interface UseSettingsOptions {
  tab: SettingsTab
}

export interface UseSettingsReturn {
  settings: Record<string, unknown>
  isLoading: boolean
  isSaving: boolean
  error: string | null
  getValue: <T>(key: string, defaultValue?: T) => T
  setValue: (key: string, value: unknown) => void
  save: () => Promise<boolean>
  reload: () => Promise<void>
}

/**
 * Hook to manage settings for a specific tab
 */
export function useSettings({ tab }: UseSettingsOptions): UseSettingsReturn {
  const [settings, setSettings] = React.useState<Record<string, unknown>>({})
  const [isLoading, setIsLoading] = React.useState(true)
  const [isSaving, setIsSaving] = React.useState(false)
  const [error, setError] = React.useState<string | null>(null)

  // Load settings on mount and when tab changes
  const loadSettings = React.useCallback(async () => {
    setIsLoading(true)
    setError(null)

    try {
      // Check preloaded cache (seeded from get_config response with all settings tabs)
      const cached = settingsCache.get(tab)
      if (cached) {
        setSettings(cached)
        setIsLoading(false)
        return
      }

      // Cache miss — tools tabs or after a save invalidation
      const data = await getTabSettings(tab)
      setSettings(data)
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to load settings')
    } finally {
      setIsLoading(false)
    }
  }, [tab])

  React.useEffect(() => {
    loadSettings()
  }, [loadSettings])

  // Get a value from settings
  const getValue = React.useCallback(
    <T>(key: string, defaultValue?: T): T => {
      const value = settings[key]
      if (value === undefined || value === null) {
        return defaultValue as T
      }
      return value as T
    },
    [settings]
  )

  // Set a value in settings (local state only)
  const setValue = React.useCallback((key: string, value: unknown) => {
    setSettings((prev) => ({
      ...prev,
      [key]: value,
    }))
  }, [])

  // Save settings to server
  const save = React.useCallback(async (): Promise<boolean> => {
    setIsSaving(true)
    setError(null)

    try {
      const result = await saveTabSettings(tab, settings)
      if (!result.success) {
        setError(result.message || 'Failed to save settings')
        return false
      }
      // Invalidate preloaded cache so next reload fetches fresh data
      settingsCache.delete(tab)
      return true
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to save settings')
      return false
    } finally {
      setIsSaving(false)
    }
  }, [tab, settings])

  return {
    settings,
    isLoading,
    isSaving,
    error,
    getValue,
    setValue,
    save,
    reload: loadSettings,
  }
}

/**
 * Hook to manage a single setting value with auto-save
 */
export function useSetting<T>(settings: UseSettingsReturn, key: string, defaultValue: T): [T, (value: T) => void] {
  const value = settings.getValue(key, defaultValue)

  const setValue = React.useCallback(
    (newValue: T) => {
      settings.setValue(key, newValue)
    },
    [settings, key]
  )

  return [value, setValue]
}
