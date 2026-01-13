/**
 * Shared route validation utilities for parsing URL search parameters.
 * Consolidates duplicated validation logic from route files.
 *
 * Filter URL format (bracket notation):
 *   filter[country]=in:JP,CN&filter[browser]=eq:Chrome
 */

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
