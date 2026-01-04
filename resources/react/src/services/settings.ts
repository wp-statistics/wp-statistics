import { clientRequest } from '@/lib/client-request'

/**
 * Settings tab names
 */
export type SettingsTab = 'general' | 'privacy' | 'notifications' | 'exclusions' | 'advanced'

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
 * Get settings for a specific tab
 */
export const getTabSettings = async (tab: SettingsTab): Promise<Record<string, unknown>> => {
  try {
    const response = await clientRequest.post<SettingsResponse>(
      '',
      {
        tab,
      },
      {
        params: {
          action: 'wp_statistics_settings_get_tab',
        },
      }
    )

    if (response.data?.success && response.data?.data?.settings) {
      return response.data.data.settings
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
    const response = await clientRequest.post<SettingsResponse>(
      '',
      {
        tab,
        settings,
      },
      {
        params: {
          action: 'wp_statistics_settings_save_tab',
        },
      }
    )

    if (response.data?.success) {
      return {
        success: true,
        message: response.data.data?.message || 'Settings saved successfully.',
      }
    }

    return {
      success: false,
      message: 'Failed to save settings.',
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
    const response = await clientRequest.post<{
      success: boolean
      data?: {
        settings: Record<SettingsTab, Record<string, unknown>>
      }
    }>(
      '',
      {},
      {
        params: {
          action: 'wp_statistics_settings_get',
        },
      }
    )

    if (response.data?.success && response.data?.data?.settings) {
      return response.data.data.settings
    }

    return {
      general: {},
      privacy: {},
      notifications: {},
      exclusions: {},
      advanced: {},
    }
  } catch (error) {
    console.error('Failed to get all settings:', error)
    return {
      general: {},
      privacy: {},
      notifications: {},
      exclusions: {},
      advanced: {},
    }
  }
}
