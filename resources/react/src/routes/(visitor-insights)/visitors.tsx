import { createFileRoute } from '@tanstack/react-router'

// Type for a single filter in URL
export interface UrlFilter {
  field: string
  operator: string
  value: string | string[]
  displayValue?: string // Display label for the value (e.g., "Iran" instead of "5")
}

// Search params type
export interface VisitorsSearchParams {
  filters?: UrlFilter[]
  page?: number
}

// Validate and parse search params
const validateSearch = (search: Record<string, unknown>): VisitorsSearchParams => {
  const result: VisitorsSearchParams = {}

  // Parse filters - handle both array and JSON string
  let filtersArray: unknown[] | undefined

  if (search.filters) {
    if (Array.isArray(search.filters)) {
      filtersArray = search.filters
    } else if (typeof search.filters === 'string') {
      try {
        // Clean the string - remove any trailing query params that might be incorrectly appended
        // This handles cases where WordPress's "?page=wp-statistics" gets mixed with hash router params
        let filterString = search.filters
        // Find the last ] that closes the JSON array, then remove anything after it
        const lastBracketIndex = filterString.lastIndexOf(']')
        if (lastBracketIndex !== -1 && lastBracketIndex < filterString.length - 1) {
          filterString = filterString.substring(0, lastBracketIndex + 1)
        }
        const parsed = JSON.parse(filterString)
        if (Array.isArray(parsed)) {
          filtersArray = parsed
        }
      } catch {
        // Invalid JSON, ignore
      }
    }
  }

  if (filtersArray) {
    result.filters = filtersArray.filter(
      (f): f is UrlFilter =>
        typeof f === 'object' &&
        f !== null &&
        typeof (f as UrlFilter).field === 'string' &&
        typeof (f as UrlFilter).operator === 'string' &&
        ((f as UrlFilter).value !== undefined)
    )
  }

  // Parse page
  if (typeof search.page === 'number' && search.page > 0) {
    result.page = search.page
  } else if (typeof search.page === 'string') {
    const parsed = parseInt(search.page, 10)
    if (!isNaN(parsed) && parsed > 0) {
      result.page = parsed
    }
  }

  return result
}

export const Route = createFileRoute('/(visitor-insights)/visitors')({
  validateSearch,
})
