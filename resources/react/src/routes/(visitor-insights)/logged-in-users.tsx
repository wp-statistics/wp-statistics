import { createFileRoute } from '@tanstack/react-router'

// Type for a single filter in URL
export interface UrlFilter {
  field: string
  operator: string
  value: string | string[]
}

// Search params type
export interface LoggedInUsersSearchParams {
  filters?: UrlFilter[]
  page?: number
}

// Validate and parse search params
const validateSearch = (search: Record<string, unknown>): LoggedInUsersSearchParams => {
  const result: LoggedInUsersSearchParams = {}

  // Parse filters - handle both array and JSON string
  let filtersArray: unknown[] | undefined

  if (search.filters) {
    if (Array.isArray(search.filters)) {
      filtersArray = search.filters
    } else if (typeof search.filters === 'string') {
      try {
        const parsed = JSON.parse(search.filters)
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

export const Route = createFileRoute('/(visitor-insights)/logged-in-users')({
  validateSearch,
})
