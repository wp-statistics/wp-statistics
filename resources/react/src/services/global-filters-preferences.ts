/**
 * Global Filters Preferences Service
 *
 * Handles saving and resetting global filter preferences via AJAX.
 * Uses debouncing to avoid excessive API calls when user rapidly changes filters.
 */

import { clientRequest } from '@/lib/client-request'

const CONTEXT = 'global_filters'

export interface GlobalFiltersPreferencesData {
  date_from?: string
  date_to?: string
  previous_date_from?: string
  previous_date_to?: string
  /** Period preset name (e.g., 'yesterday', 'last30') for dynamic date resolution */
  period?: string
  filters?: PersistedUrlFilter[]
}

interface SaveResponse {
  success: boolean
  data?: {
    success: boolean
    message: string
  }
  error?: string
}

// Debounce tracking
let saveTimeout: ReturnType<typeof setTimeout> | null = null
let pendingData: GlobalFiltersPreferencesData | null = null

/**
 * Cancel any pending save operations
 * Call this before reset to prevent saves from overwriting reset
 */
export const cancelPendingSave = () => {
  if (saveTimeout) {
    clearTimeout(saveTimeout)
    saveTimeout = null
  }
  pendingData = null
}

/**
 * Save global filters preferences to the backend
 * Debounced to avoid excessive API calls (max 1 save per 500ms)
 */
export const saveGlobalFiltersPreferences = async (data: GlobalFiltersPreferencesData): Promise<void> => {
  // Store the latest data
  pendingData = data

  // Clear any existing timeout
  if (saveTimeout) {
    clearTimeout(saveTimeout)
  }

  // Create a new debounced save
  return new Promise((resolve) => {
    saveTimeout = setTimeout(async () => {
      if (!pendingData) {
        resolve()
        return
      }

      const dataToSave = pendingData
      pendingData = null

      try {
        await clientRequest.post<SaveResponse>(
          '',
          {
            action_type: 'save',
            context: CONTEXT,
            data: dataToSave,
          },
          {
            params: {
              action: 'wp_statistics_user_preferences',
            },
          }
        )
      } catch (error) {
        console.error('Failed to save global filters preferences:', error)
      }

      resolve()
    }, 500)
  })
}

/**
 * Reset global filters preferences to defaults
 * Clears saved preferences from the database
 */
export const resetGlobalFiltersPreferences = async (): Promise<void> => {
  // Cancel any pending saves to prevent them from overwriting the reset
  cancelPendingSave()

  try {
    await clientRequest.post<SaveResponse>(
      '',
      {
        action_type: 'reset',
        context: CONTEXT,
      },
      {
        params: {
          action: 'wp_statistics_user_preferences',
        },
      }
    )
  } catch (error) {
    console.error('Failed to reset global filters preferences:', error)
  }
}
