import { clientRequest } from '@/lib/client-request'

const STORAGE_PREFIX = 'wp_statistics_page_options_'

export interface PageOptionsPreferences {
  widgets?: Record<string, boolean>
  metrics?: Record<string, boolean>
}

interface SavePreferencesResponse {
  success: boolean
  data?: {
    success: boolean
    message: string
  }
  error?: string
}

// Debounce tracking
let saveTimeout: ReturnType<typeof setTimeout> | null = null
let pendingPageId: string | null = null
let pendingPreferences: PageOptionsPreferences | null = null

/**
 * Cancel any pending save operations
 */
export const cancelPendingSave = () => {
  if (saveTimeout) {
    clearTimeout(saveTimeout)
    saveTimeout = null
  }
  pendingPageId = null
  pendingPreferences = null
}

/**
 * Save page options preferences to the backend
 * Debounced to avoid excessive API calls (max 1 save per 500ms)
 */
export const savePageOptionsPreferences = async (
  pageId: string,
  preferences: PageOptionsPreferences
): Promise<void> => {
  pendingPageId = pageId
  pendingPreferences = preferences

  if (saveTimeout) {
    clearTimeout(saveTimeout)
  }

  return new Promise((resolve) => {
    saveTimeout = setTimeout(async () => {
      if (!pendingPageId || !pendingPreferences) {
        resolve()
        return
      }

      const currentPageId = pendingPageId
      const currentPreferences = pendingPreferences
      pendingPageId = null
      pendingPreferences = null

      try {
        await clientRequest.post<SavePreferencesResponse>(
          '',
          {
            action_type: 'save',
            context: currentPageId,
            data: currentPreferences,
          },
          {
            params: {
              action: 'wp_statistics_user_preferences',
            },
          }
        )
      } catch (error) {
        console.error('Failed to save page options preferences:', error)
      }

      resolve()
    }, 500)
  })
}

/**
 * Reset page options preferences to defaults
 */
export const resetPageOptionsPreferences = async (pageId: string): Promise<void> => {
  cancelPendingSave()

  try {
    await clientRequest.post<SavePreferencesResponse>(
      '',
      {
        action_type: 'reset',
        context: pageId,
      },
      {
        params: {
          action: 'wp_statistics_user_preferences',
        },
      }
    )
  } catch (error) {
    console.error('Failed to reset page options preferences:', error)
  }
}

/**
 * Get cached page options from localStorage
 */
export const getCachedPageOptions = (pageId: string): PageOptionsPreferences | null => {
  try {
    const cached = localStorage.getItem(`${STORAGE_PREFIX}${pageId}`)
    if (cached) {
      return JSON.parse(cached)
    }
  } catch {
    // Ignore localStorage errors
  }
  return null
}

/**
 * Set cached page options in localStorage
 */
export const setCachedPageOptions = (pageId: string, preferences: PageOptionsPreferences): void => {
  try {
    localStorage.setItem(`${STORAGE_PREFIX}${pageId}`, JSON.stringify(preferences))
  } catch {
    // Ignore localStorage errors
  }
}

/**
 * Clear cached page options from localStorage
 */
export const clearCachedPageOptions = (pageId: string): void => {
  try {
    localStorage.removeItem(`${STORAGE_PREFIX}${pageId}`)
  } catch {
    // Ignore localStorage errors
  }
}
