import { __ } from '@wordpress/i18n'

import { WordPress } from '@/lib/wordpress'

/**
 * Settings tab names.
 *
 * Extensible string type — premium and third-party plugins can add new tabs
 * via the wp_statistics_settings_tabs PHP filter.
 */
export type SettingsTab = string

/**
 * Settings response from AJAX
 */
export interface SettingsResponse {
  success: boolean
  data?: {
    tab?: string
    settings: Record<string, unknown>
    message?: string
  }
}

/**
 * Build FormData for AJAX requests with nonce
 */
const buildFormData = (action: string, data: Record<string, unknown> = {}): FormData => {
  const wp = WordPress.getInstance()
  const formData = new FormData()

  formData.append('action', action)
  formData.append('wps_nonce', wp.getNonce())

  // Append each data field
  for (const [key, value] of Object.entries(data)) {
    if (value !== undefined && value !== null) {
      if (typeof value === 'object') {
        formData.append(key, JSON.stringify(value))
      } else {
        formData.append(key, String(value))
      }
    }
  }

  return formData
}

/**
 * Make AJAX request with FormData
 */
const ajaxRequest = async <T>(action: string, data: Record<string, unknown> = {}): Promise<T> => {
  const wp = WordPress.getInstance()
  const formData = buildFormData(action, data)

  const response = await fetch(wp.getAjaxUrl(), {
    method: 'POST',
    body: formData,
    credentials: 'same-origin',
  })

  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`)
  }

  return response.json()
}

/**
 * Get settings for a specific tab
 */
export const getTabSettings = async (tab: SettingsTab): Promise<Record<string, unknown>> => {
  try {
    const response = await ajaxRequest<SettingsResponse>('wp_statistics_settings_get_tab', { tab })

    if (response?.success && response?.data?.settings) {
      return response.data.settings
    }

    return {}
  } catch (error) {
    console.error('Failed to get settings:', error)
    return {}
  }
}

/**
 * Save settings for a specific tab
 */
export const saveTabSettings = async (
  tab: SettingsTab,
  settings: Record<string, unknown>
): Promise<{ success: boolean; message?: string }> => {
  try {
    const response = await ajaxRequest<SettingsResponse>('wp_statistics_settings_save_tab', {
      tab,
      settings,
    })

    if (response?.success) {
      return {
        success: true,
        message: response.data?.message || 'Settings saved successfully.',
      }
    }

    return {
      success: false,
      message: (response?.data as { message?: string })?.message || 'Failed to save settings.',
    }
  } catch (error) {
    console.error('Failed to save settings:', error)
    return {
      success: false,
      message: error instanceof Error ? error.message : 'Failed to save settings.',
    }
  }
}

/**
 * Get all settings
 */
export const getAllSettings = async (): Promise<Record<SettingsTab, Record<string, unknown>>> => {
  try {
    const response = await ajaxRequest<{
      success: boolean
      data?: {
        settings: Record<SettingsTab, Record<string, unknown>>
      }
    }>('wp_statistics_settings_get', {})

    if (response?.success && response?.data?.settings) {
      return response.data.settings
    }

    return {
      general: {},
      privacy: {},
      notifications: {},
      exclusions: {},
      advanced: {},
      display: {},
      access: {},
      data: {},
    }
  } catch (error) {
    console.error('Failed to get all settings:', error)
    return {
      general: {},
      privacy: {},
      notifications: {},
      exclusions: {},
      advanced: {},
      display: {},
      access: {},
      data: {},
    }
  }
}

/**
 * Preview email report (opens in new window)
 */
export const previewEmail = async (): Promise<{ success: boolean; html?: string; message?: string }> => {
  try {
    const response = await ajaxRequest<{
      success: boolean
      data: { html?: string; message?: string }
    }>('wp_statistics_email_preview', {})

    if (response?.success && response?.data?.html) {
      return { success: true, html: response.data.html }
    }

    return { success: false, message: response?.data?.message || __('Unable to generate preview.', 'wp-statistics') }
  } catch (error) {
    return { success: false, message: error instanceof Error ? error.message : __('Failed to generate preview.', 'wp-statistics') }
  }
}

/**
 * Send test email
 */
export const sendTestEmail = async (): Promise<{ success: boolean; email?: string; message?: string }> => {
  try {
    const response = await ajaxRequest<{
      success: boolean
      data: { email?: string; message?: string }
    }>('wp_statistics_email_send_test', {})

    if (response?.success) {
      return { success: true, email: (response.data as { email?: string })?.email }
    }

    return { success: false, message: (response?.data as { message?: string })?.message || __('Failed to send test email.', 'wp-statistics') }
  } catch (error) {
    return { success: false, message: error instanceof Error ? error.message : __('Failed to send test email.', 'wp-statistics') }
  }
}
