/**
 * Shared route validation utilities for parsing URL search parameters.
 * Consolidates duplicated validation logic from route files.
 *
 * Supports two URL formats for filters:
 *
 * 1. Bracket notation (preferred):
 *    filter[country]=in:JP,CN&filter[browser]=eq:Chrome
 *
 * 2. Legacy JSON format (for backward compatibility):
 *    filters=[{"field":"country","operator":"in","value":["JP","CN"]}]
 */

import { parseBracketFilter, parseLegacyJsonFilters } from './filter-utils'

/**
 * Represents a single filter in URL search params
 */
export interface UrlFilter {
  field: string
  operator: string
  value: string | string[]
  displayValue?: string
}

/**
 * Base search params that all routes can extend.
 * Note: 'filters' is NOT included here to prevent TanStack Router from serializing
 * it as JSON. Filters are stored as bracket notation params (filter[field]=op:value)
 * and parsed by global-filters-context.tsx.
 */
export interface BaseSearchParams {
  page?: number
  // Global date params for hybrid URL + preferences approach
  date_from?: string
  date_to?: string
  previous_date_from?: string
  previous_date_to?: string
  // Bracket notation filter params are passed through as strings
  // e.g., 'filter[country]': 'eq:US'
  [key: `filter[${string}]`]: string
}

/**
 * Options for search validator creation
 */
export interface SearchValidatorOptions {
  /**
   * Whether to include page parameter parsing
   * @default true
   */
  includePage?: boolean
}

/**
 * Type guard to check if a value is a valid UrlFilter
 */
const isUrlFilter = (f: unknown): f is UrlFilter =>
  typeof f === 'object' &&
  f !== null &&
  typeof (f as UrlFilter).field === 'string' &&
  typeof (f as UrlFilter).operator === 'string' &&
  (f as UrlFilter).value !== undefined

/**
 * Parse bracket notation filters from search params object
 * e.g., { "filter[country]": "in:JP,CN", "filter[browser]": "eq:Chrome" }
 */
const parseBracketFilters = (search: Record<string, unknown>): UrlFilter[] => {
  const filters: UrlFilter[] = []

  for (const [key, value] of Object.entries(search)) {
    if (key.startsWith('filter[') && typeof value === 'string') {
      const filter = parseBracketFilter(key, value)
      if (filter) {
        filters.push(filter)
      }
    }
  }

  return filters
}

/**
 * Parses legacy JSON filter string from URL, handling WordPress query param interference.
 * WordPress's "?page=wp-statistics" can get mixed with hash router params,
 * causing malformed JSON. This function cleans the string before parsing.
 */
const parseLegacyFilterString = (filterString: string): UrlFilter[] => {
  return parseLegacyJsonFilters(filterString)
}

/**
 * Parses filters from search params
 * Supports both bracket notation and legacy JSON format
 */
const parseFilters = (search: Record<string, unknown>): UrlFilter[] | undefined => {
  // First, try bracket notation (preferred format)
  const bracketFilters = parseBracketFilters(search)
  if (bracketFilters.length > 0) {
    return bracketFilters
  }

  // Fall back to legacy JSON format
  const legacyFilters = search.filters
  if (legacyFilters) {
    let filtersArray: UrlFilter[] = []

    if (Array.isArray(legacyFilters)) {
      // Already an array (from router parsing)
      filtersArray = legacyFilters.filter(isUrlFilter)
    } else if (typeof legacyFilters === 'string') {
      // JSON string
      filtersArray = parseLegacyFilterString(legacyFilters)
    }

    return filtersArray.length > 0 ? filtersArray : undefined
  }

  return undefined
}

/**
 * Parses page number from search params
 */
const parsePage = (searchPage: unknown): number | undefined => {
  if (typeof searchPage === 'number' && searchPage > 0) {
    return searchPage
  }
  if (typeof searchPage === 'string') {
    const parsed = parseInt(searchPage, 10)
    if (!isNaN(parsed) && parsed > 0) {
      return parsed
    }
  }
  return undefined
}

/**
 * Parses date string from search params (YYYY-MM-DD format)
 */
const parseDate = (searchDate: unknown): string | undefined => {
  if (typeof searchDate !== 'string') return undefined
  // Validate YYYY-MM-DD format
  if (!/^\d{4}-\d{2}-\d{2}$/.test(searchDate)) return undefined
  return searchDate
}

/**
 * Creates a search params validator function for TanStack Router.
 *
 * @example
 * // With page support (default)
 * export const Route = createFileRoute('/visitors')({
 *   validateSearch: createSearchValidator(),
 * })
 *
 * @example
 * // Without page support
 * export const Route = createFileRoute('/overview')({
 *   validateSearch: createSearchValidator({ includePage: false }),
 * })
 */
export function createSearchValidator<T extends BaseSearchParams = BaseSearchParams>(
  options: SearchValidatorOptions = {}
): (search: Record<string, unknown>) => T {
  const { includePage = true } = options

  return (search: Record<string, unknown>): T => {
    const result: Record<string, unknown> = {}

    // Pass through bracket notation filter params as-is (they're strings)
    // These will be parsed by global-filters-context.tsx
    // DO NOT parse filters here and add as 'filters' array - TanStack Router would serialize it as JSON
    for (const [key, value] of Object.entries(search)) {
      if (key.startsWith('filter[') && typeof value === 'string') {
        result[key] = value
      }
    }

    // Handle legacy JSON 'filters' param - convert to bracket notation
    // This ensures backward compatibility while preventing re-serialization
    if (search.filters && !Object.keys(result).some(k => k.startsWith('filter['))) {
      const legacyFilters = parseFilters(search)
      if (legacyFilters) {
        for (const filter of legacyFilters) {
          const values = Array.isArray(filter.value) ? filter.value : [filter.value]
          result[`filter[${filter.field}]`] = `${filter.operator}:${values.join(',')}`
        }
      }
    }

    if (includePage) {
      const page = parsePage(search.page)
      if (page) {
        result.page = page
      }
    }

    // Parse date params for global filters
    const dateFrom = parseDate(search.date_from)
    const dateTo = parseDate(search.date_to)
    // Only include dates if both are present
    if (dateFrom && dateTo) {
      result.date_from = dateFrom
      result.date_to = dateTo

      // Compare dates only matter if main dates are present
      const previousDateFrom = parseDate(search.previous_date_from)
      const previousDateTo = parseDate(search.previous_date_to)
      if (previousDateFrom && previousDateTo) {
        result.previous_date_from = previousDateFrom
        result.previous_date_to = previousDateTo
      }
    }

    return result as T
  }
}

/**
 * Pre-configured validators for common use cases
 */
export const searchValidators = {
  /**
   * Validator with filters and page support
   */
  withPage: createSearchValidator({ includePage: true }),

  /**
   * Validator with filters only (no page support)
   */
  filtersOnly: createSearchValidator({ includePage: false }),
} as const
