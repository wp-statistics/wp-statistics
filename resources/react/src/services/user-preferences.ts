import { clientRequest } from '@/lib/client-request'

export interface SaveUserPreferencesParams {
  context: string
  columns: string[] // Array of visible column IDs in their display order
  comparisonColumns?: string[] // Array of column IDs that should show PP comparison
}

export interface SaveUserPreferencesResponse {
  success: boolean
  data?: {
    success: boolean
    message: string
  }
  error?: string
}

// Debounce tracking
let saveTimeout: ReturnType<typeof setTimeout> | null = null
let pendingPreferences: SaveUserPreferencesParams | null = null

/**
 * Cancel any pending save operations
 * Call this before reset to prevent saves from overwriting reset
 */
export const cancelPendingSave = () => {
  if (saveTimeout) {
    clearTimeout(saveTimeout)
    saveTimeout = null
  }
  pendingPreferences = null
}

/**
 * Save user preferences to the backend
 * Debounced to avoid excessive API calls (max 1 save per 500ms)
 */
export const saveUserPreferences = async (params: SaveUserPreferencesParams): Promise<void> => {
  // Store the latest preferences
  pendingPreferences = params

  // Clear any existing timeout
  if (saveTimeout) {
    clearTimeout(saveTimeout)
  }

  // Create a new debounced save
  return new Promise((resolve) => {
    saveTimeout = setTimeout(async () => {
      if (!pendingPreferences) {
        resolve()
        return
      }

      const { context, columns, comparisonColumns } = pendingPreferences
      pendingPreferences = null

      try {
        await clientRequest.post<SaveUserPreferencesResponse>(
          '',
          {
            action_type: 'save',
            context,
            data: {
              columns,
              ...(comparisonColumns !== undefined && { comparison_columns: comparisonColumns }),
            },
          },
          {
            params: {
              action: 'wp_statistics_user_preferences',
            },
          }
        )
      } catch (error) {
        console.error('Failed to save user preferences:', error)
      }

      resolve()
    }, 500)
  })
}

export interface ResetUserPreferencesParams {
  context: string
}

/**
 * Reset user preferences to defaults
 */
export const resetUserPreferences = async (params: ResetUserPreferencesParams): Promise<void> => {
  // Cancel any pending saves to prevent them from overwriting the reset
  cancelPendingSave()

  const { context } = params

  try {
    await clientRequest.post<SaveUserPreferencesResponse>(
      '',
      {
        action_type: 'reset',
        context,
      },
      {
        params: {
          action: 'wp_statistics_user_preferences',
        },
      }
    )
  } catch (error) {
    console.error('Failed to reset user preferences:', error)
  }
}

/**
 * Convert column visibility state and order to array of visible column IDs
 * The array order represents the display order
 *
 * @param visibleColumns - The visibility state (columns not in object are considered visible)
 * @param columnOrder - The column order array (if empty, uses allColumnIds order)
 * @param allColumnIds - All available column IDs (required to properly determine visible columns)
 */
export const createVisibleColumnsArray = (
  visibleColumns: Record<string, boolean>,
  columnOrder: string[],
  allColumnIds: string[] = []
): string[] => {
  // If we have a column order, use it and filter to only visible columns
  if (columnOrder.length > 0) {
    return columnOrder.filter((columnId) => visibleColumns[columnId] !== false)
  }

  // If we have allColumnIds, use them to determine visible columns
  // (columns not in visibleColumns are considered visible by default)
  if (allColumnIds.length > 0) {
    return allColumnIds.filter((columnId) => visibleColumns[columnId] !== false)
  }

  // Fallback: return all columns from visibility state that are explicitly true
  return Object.entries(visibleColumns)
    .filter(([, isVisible]) => isVisible)
    .map(([columnId]) => columnId)
}

/**
 * Parse column preferences from API response
 * The columns array contains visible column IDs in their display order
 * Returns the visible columns set (for use with allColumns to determine hidden)
 */
export const parseColumnPreferences = (
  columns: string[] | undefined
): {
  columnVisibility: Record<string, boolean>
  columnOrder: string[]
  visibleColumnsSet: Set<string>
} => {
  if (!columns || columns.length === 0) {
    return {
      columnVisibility: {},
      columnOrder: [],
      visibleColumnsSet: new Set(),
    }
  }

  // All columns in the array are visible, order is the array order
  const columnVisibility: Record<string, boolean> = {}
  const visibleColumnsSet = new Set<string>()
  columns.forEach((columnId) => {
    columnVisibility[columnId] = true
    visibleColumnsSet.add(columnId)
  })

  return {
    columnVisibility,
    columnOrder: columns,
    visibleColumnsSet,
  }
}

/**
 * Compute full visibility state by marking columns not in preferences as hidden
 */
export const computeFullVisibility = (
  visibleColumnsSet: Set<string>,
  allColumnIds: string[]
): Record<string, boolean> => {
  const visibility: Record<string, boolean> = {}
  allColumnIds.forEach((columnId) => {
    visibility[columnId] = visibleColumnsSet.has(columnId)
  })
  return visibility
}
