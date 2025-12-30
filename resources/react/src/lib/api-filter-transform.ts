/**
 * Shared filter transformation utilities for API requests.
 * Consolidates duplicated logic from service files.
 */

import type { Filter } from '@/components/custom/filter-bar'

/**
 * API filter format: { filter_key: { operator: value } }
 * Example: { "os": { "eq": "Windows" }, "browser": { "in": ["Chrome", "Firefox"] } }
 */
export type ApiFilters = Record<string, Record<string, string | string[]>>

/**
 * Extracts the field name from a filter ID.
 *
 * Filter IDs are in the format: "os-os-filter-1766484171552-9509610"
 * where the first segment is the actual field name.
 *
 * @param filterId - The full filter ID string
 * @returns The extracted field name (e.g., 'os' from 'os-os-filter-...')
 */
export const extractFilterKey = (filterId: string): string => {
  return filterId.split('-')[0]
}

/**
 * Transforms UI filter objects to the API filter format.
 *
 * UI Filter: { id, label, operator, rawOperator, value, rawValue }
 * API Format: { filter_key: { operator: value } }
 *
 * @param filters - Array of UI filter objects
 * @returns API-formatted filter object
 *
 * @example
 * // Single filter
 * transformFiltersToApi([{ id: 'os-123', operator: 'eq', value: 'Windows' }])
 * // Returns: { "os": { "eq": "Windows" } }
 *
 * @example
 * // Multiple values
 * transformFiltersToApi([{ id: 'browser-456', operator: 'in', value: ['Chrome', 'Firefox'] }])
 * // Returns: { "browser": { "in": ["Chrome", "Firefox"] } }
 */
export const transformFiltersToApi = (filters: Filter[]): ApiFilters => {
  const apiFilters: ApiFilters = {}

  for (const filter of filters) {
    // Extract the field name from the filter id
    const filterKey = extractFilterKey(filter.id)
    // Use rawOperator if available, otherwise fall back to operator
    const operator = filter.rawOperator || filter.operator
    // Use rawValue if available, otherwise fall back to value
    // Convert number to string since API expects string | string[]
    const rawValue = filter.rawValue ?? filter.value
    const value: string | string[] = Array.isArray(rawValue)
      ? rawValue
      : typeof rawValue === 'number'
        ? String(rawValue)
        : rawValue

    apiFilters[filterKey] = {
      [operator]: value,
    }
  }

  return apiFilters
}
