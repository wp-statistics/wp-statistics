import { WordPress } from '@/lib/wordpress'

// ── Types ────────────────────────────────────────────────────────────

export interface SettingsTabConfig {
  area: 'settings' | 'tools'
  label: string
  icon: string
  order: number
  save_description?: string
  tab_key?: string
  component?: string
}

export interface SettingsCardConfig {
  title: string
  description?: string
  order: number
  type?: 'component'
  component?: string
  variant?: 'danger'
  visible_when?: Record<string, unknown>
}

export interface SettingsFieldConfig {
  type: 'toggle' | 'select' | 'input' | 'textarea' | 'number' | 'action' | 'notice' | 'component'
  setting_key?: string
  label?: string
  description?: string
  default?: unknown
  order: number
  // Toggle
  inverted?: boolean
  // Select
  options?: { value: string; label: string }[]
  layout?: 'inline' | 'stacked'
  placeholder?: string
  // Input / Textarea
  rows?: number
  // Number
  min?: number
  max?: number
  // Nested / conditional
  nested?: boolean
  visible_when?: Record<string, unknown>
  // Notice
  notice_type?: 'warning' | 'info'
  message?: string
  help_url?: string
  // Action
  action?: string
  variant?: string
  // Component
  component?: string
}

export interface SettingsConfig {
  tabs: Record<string, SettingsTabConfig>
  cards: Record<string, Record<string, SettingsCardConfig>>
  fields: Record<string, Record<string, SettingsFieldConfig>>
}

// ── Settings cache (seeded from initial config response) ────────────

/**
 * One-time cache for initial tab settings returned alongside config.
 * Consumed by use-settings.ts to avoid a second AJAX call on page load.
 */
export const settingsCache = new Map<string, Record<string, unknown>>()

// ── AJAX fetcher ─────────────────────────────────────────────────────

export async function fetchSettingsConfig(initialTab?: string): Promise<SettingsConfig> {
  const wp = WordPress.getInstance()
  const formData = new FormData()
  formData.append('action', 'wp_statistics_settings')
  formData.append('sub_action', 'get_config')
  formData.append('wps_nonce', wp.getNonce())

  if (initialTab) {
    formData.append('tab', initialTab)
  }

  const response = await fetch(wp.getAjaxUrl(), {
    method: 'POST',
    body: formData,
    credentials: 'same-origin',
  })

  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`)
  }

  const json = await response.json()

  if (!json?.success || !json?.data) {
    throw new Error('Failed to load settings config')
  }

  const data = json.data

  // Seed the settings cache with ALL settings-area tab values.
  // This eliminates per-tab AJAX requests for settings tabs.
  if (data.all_settings && typeof data.all_settings === 'object') {
    for (const [tab, values] of Object.entries(data.all_settings)) {
      settingsCache.set(tab, values as Record<string, unknown>)
    }
  }

  return {
    tabs: data.tabs,
    cards: data.cards,
    fields: data.fields,
  } as SettingsConfig
}
