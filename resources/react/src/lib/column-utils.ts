/**
 * Column management utilities for DataTable components
 * Extracted from route files to reduce code duplication
 */

export interface ColumnConfig {
  baseColumns: string[]
  columnDependencies: Record<string, string[]>
  context: string
}

/**
 * Compute API columns based on visible UI columns and current sort column
 * In TanStack Table, columns NOT in visibleColumns are considered visible by default
 */
export const computeApiColumns = (
  visibleColumns: Record<string, boolean>,
  allColumnIds: string[],
  config: ColumnConfig,
  sortColumn?: string
): string[] => {
  const apiColumns = new Set<string>(config.baseColumns)

  // For each column, check if it's visible (not explicitly set to false)
  allColumnIds.forEach((columnId) => {
    const isVisible = visibleColumns[columnId] !== false // undefined or true = visible
    if (isVisible && config.columnDependencies[columnId]) {
      config.columnDependencies[columnId].forEach((apiCol) => apiColumns.add(apiCol))
    }
  })

  // Always include the sort column's dependencies to ensure ORDER BY works
  if (sortColumn && config.columnDependencies[sortColumn]) {
    config.columnDependencies[sortColumn].forEach((apiCol) => apiColumns.add(apiCol))
  }

  return Array.from(apiColumns)
}

/**
 * Get visible columns for saving preferences
 * Respects columnOrder for ordering, but includes ALL visible columns
 */
export const getVisibleColumnsForSave = (
  visibility: Record<string, boolean>,
  columnOrder: string[],
  allColumnIds: string[]
): string[] => {
  // Get all visible column IDs (not explicitly set to false)
  const visibleSet = new Set(allColumnIds.filter((col) => visibility[col] !== false))

  if (columnOrder.length === 0) {
    // No custom order, return all visible columns in default order
    return allColumnIds.filter((col) => visibleSet.has(col))
  }

  // Build result: ordered columns first, then any visible columns not in order
  const result: string[] = []
  const addedSet = new Set<string>()

  // First, add columns from columnOrder that are visible
  for (const col of columnOrder) {
    if (visibleSet.has(col) && !addedSet.has(col)) {
      result.push(col)
      addedSet.add(col)
    }
  }

  // Then add any visible columns not yet in result (maintains their relative order from allColumnIds)
  for (const col of allColumnIds) {
    if (visibleSet.has(col) && !addedSet.has(col)) {
      result.push(col)
      addedSet.add(col)
    }
  }

  return result
}

/**
 * Get default API columns (all columns visible)
 */
export const getDefaultApiColumns = (config: ColumnConfig): string[] => {
  return [...config.baseColumns, ...Object.values(config.columnDependencies).flat()].filter(
    (col, index, arr) => arr.indexOf(col) === index
  )
}

/**
 * Get cache key for localStorage
 */
export const getCacheKey = (context: string): string => {
  return `wp_statistics_columns_${context}`
}

/**
 * Get cache key for comparison columns in localStorage
 */
export const getComparisonCacheKey = (context: string): string => {
  return `wp_statistics_comparison_${context}`
}

/**
 * Get cached API columns from localStorage
 */
export const getCachedApiColumns = (allColumnIds: string[], config: ColumnConfig): string[] | null => {
  try {
    const cacheKey = getCacheKey(config.context)
    const cached = localStorage.getItem(cacheKey)
    if (!cached) return null
    const visibleColumns = JSON.parse(cached) as string[]
    if (!Array.isArray(visibleColumns) || visibleColumns.length === 0) return null
    // Convert visible UI columns to API columns
    const apiColumns = new Set<string>(config.baseColumns)
    visibleColumns.forEach((columnId) => {
      if (config.columnDependencies[columnId]) {
        config.columnDependencies[columnId].forEach((apiCol) => apiColumns.add(apiCol))
      }
    })
    return Array.from(apiColumns)
  } catch {
    return null
  }
}

/**
 * Save visible columns to localStorage cache
 */
export const setCachedColumns = (context: string, visibleColumns: string[]): void => {
  try {
    const cacheKey = getCacheKey(context)
    localStorage.setItem(cacheKey, JSON.stringify(visibleColumns))
  } catch {
    // Ignore storage errors
  }
}

/**
 * Clear cached columns from localStorage
 */
export const clearCachedColumns = (context: string): void => {
  try {
    const cacheKey = getCacheKey(context)
    localStorage.removeItem(cacheKey)
  } catch {
    // Ignore storage errors
  }
}

/**
 * Get cached visible columns from localStorage (raw column IDs)
 */
export const getCachedVisibleColumns = (context: string): string[] | null => {
  try {
    const cacheKey = getCacheKey(context)
    const cached = localStorage.getItem(cacheKey)
    if (!cached) return null
    const visibleColumns = JSON.parse(cached) as string[]
    if (!Array.isArray(visibleColumns) || visibleColumns.length === 0) return null
    return visibleColumns
  } catch {
    return null
  }
}

/**
 * Get cached visibility state for TanStack Table
 * Returns a Record where visible columns are true and hidden columns are false
 */
export const getCachedVisibility = (context: string, allColumnIds: string[]): Record<string, boolean> | null => {
  const cachedColumns = getCachedVisibleColumns(context)
  if (!cachedColumns) return null

  // Create visibility object: columns in cache are visible, others are hidden
  const visibility: Record<string, boolean> = {}
  allColumnIds.forEach((col) => {
    visibility[col] = cachedColumns.includes(col)
  })
  return visibility
}

/**
 * Get cached comparison columns from localStorage
 */
export const getCachedComparisonColumns = (context: string): string[] | null => {
  try {
    const cacheKey = getComparisonCacheKey(context)
    const cached = localStorage.getItem(cacheKey)
    if (!cached) return null
    const comparisonColumns = JSON.parse(cached) as string[]
    if (!Array.isArray(comparisonColumns)) return null
    return comparisonColumns
  } catch {
    return null
  }
}

/**
 * Save comparison columns to localStorage cache
 */
export const setCachedComparisonColumns = (context: string, comparisonColumns: string[]): void => {
  try {
    const cacheKey = getComparisonCacheKey(context)
    localStorage.setItem(cacheKey, JSON.stringify(comparisonColumns))
  } catch {
    // Ignore storage errors
  }
}

/**
 * Clear cached comparison columns from localStorage
 */
export const clearCachedComparisonColumns = (context: string): void => {
  try {
    const cacheKey = getComparisonCacheKey(context)
    localStorage.removeItem(cacheKey)
  } catch {
    // Ignore storage errors
  }
}

/**
 * Get the API field name for sorting based on column ID
 * Uses the first dependency field from columnDependencies as the sort field
 * Returns the columnId if no mapping exists (assumes it matches API field)
 */
export const getApiSortField = (columnId: string, config: ColumnConfig): string => {
  const dependencies = config.columnDependencies[columnId]
  if (dependencies && dependencies.length > 0) {
    return dependencies[0]
  }
  return columnId
}
