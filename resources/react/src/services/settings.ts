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
 * Internal helper — POST to the single wp_statistics_settings endpoint.
 */
async function ajaxPost(formData: FormData) {
  const wp = WordPress.getInstance()
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
 * Call the wp_statistics_settings AJAX endpoint with a sub_action.
 * Mirrors the callToolsApi / callImportExportApi pattern from tools.ts.
 */
export const callSettingsApi = async (
  subAction: string,
  params: Record<string, unknown> = {}
) => {
  const wp = WordPress.getInstance()
  const formData = new FormData()
  formData.append('action', 'wp_statistics_settings')
  formData.append('sub_action', subAction)
  formData.append('wps_nonce', wp.getNonce())

  for (const [key, value] of Object.entries(params)) {
    if (value !== undefined && value !== null) {
      if (typeof value === 'object') {
        formData.append(key, JSON.stringify(value))
      } else {
        formData.append(key, String(value))
      }
    }
  }

  return ajaxPost(formData)
}

/**
 * Get settings for a specific tab
 */
export const getTabSettings = async (tab: SettingsTab): Promise<Record<string, unknown>> => {
  try {
    const response = await callSettingsApi('get_tab', { tab })

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
    const response = await callSettingsApi('save_tab', { tab, settings })

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
    const response = await callSettingsApi('get', {})

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
    const response = await callSettingsApi('email_preview', {})

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
export const sendTestEmail = async (email: string): Promise<{ success: boolean; email?: string; message?: string }> => {
  try {
    const response = await callSettingsApi('email_send', { email })

    if (response?.success) {
      return { success: true, email: (response.data as { email?: string })?.email }
    }

    return { success: false, message: (response?.data as { message?: string })?.message || __('Failed to send test email.', 'wp-statistics') }
  } catch (error) {
    return { success: false, message: error instanceof Error ? error.message : __('Failed to send test email.', 'wp-statistics') }
  }
}
