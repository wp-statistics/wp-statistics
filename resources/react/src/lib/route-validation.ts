/**
 * Shared route validation utilities for parsing URL search parameters.
 * Consolidates duplicated validation logic from route files.
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
 * Base search params that all routes can extend
 */
export interface BaseSearchParams {
  filters?: UrlFilter[]
  page?: number
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
 * Parses filter string from URL, handling WordPress query param interference.
 * WordPress's "?page=wp-statistics" can get mixed with hash router params,
 * causing malformed JSON. This function cleans the string before parsing.
 */
const parseFilterString = (filterString: string): unknown[] | undefined => {
  try {
    let cleanString = filterString
    // Find the last ] that closes the JSON array, then remove anything after it
    const lastBracketIndex = cleanString.lastIndexOf(']')
    if (lastBracketIndex !== -1 && lastBracketIndex < cleanString.length - 1) {
      cleanString = cleanString.substring(0, lastBracketIndex + 1)
    }
    const parsed = JSON.parse(cleanString)
    if (Array.isArray(parsed)) {
      return parsed
    }
  } catch {
    // Invalid JSON, ignore
  }
  return undefined
}

/**
 * Parses filters from search params (handles both array and JSON string)
 */
const parseFilters = (searchFilters: unknown): UrlFilter[] | undefined => {
  let filtersArray: unknown[] | undefined

  if (searchFilters) {
    if (Array.isArray(searchFilters)) {
      filtersArray = searchFilters
    } else if (typeof searchFilters === 'string') {
      filtersArray = parseFilterString(searchFilters)
    }
  }

  if (filtersArray) {
    const validFilters = filtersArray.filter(isUrlFilter)
    return validFilters.length > 0 ? validFilters : undefined
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
    const result: BaseSearchParams = {}

    const filters = parseFilters(search.filters)
    if (filters) {
      result.filters = filters
    }

    if (includePage) {
      const page = parsePage(search.page)
      if (page) {
        result.page = page
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
