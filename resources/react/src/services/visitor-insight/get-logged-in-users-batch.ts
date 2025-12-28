import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

import type { LoggedInUser } from './get-logged-in-users'

// API filter format: { filter_key: { operator: value } }
type ApiFilters = Record<string, Record<string, string | string[]>>

// Extract the field name from filter ID
const extractFilterKey = (filterId: string): string => {
  return filterId.split('-')[0]
}

// Transform UI filters to API format
const transformFiltersToApi = (filters: Filter[]): ApiFilters => {
  const apiFilters: ApiFilters = {}

  for (const filter of filters) {
    const filterKey = extractFilterKey(filter.id)
    const operator = filter.rawOperator || filter.operator
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

// Map frontend column names to API column names
const columnMapping: Record<string, string> = {
  visitorInfo: 'visitor_id',
  lastVisit: 'last_visit',
  page: 'entry_page',
  referrer: 'referrer_domain',
  entryPage: 'entry_page',
  totalViews: 'total_views',
}

// Chart format response (for traffic trends)
export interface TrafficTrendsChartResult {
  success: boolean
  labels: string[]
  datasets: Array<{
    key: string
    label: string
    data: (number | string)[]
    comparison?: boolean
  }>
  meta?: Record<string, unknown>
}

// Response types for each query in the batch
interface LoggedInUsersTableResult {
  success: boolean
  data: {
    rows: LoggedInUser[]
    total: number
    totals?: {
      visitors: number
    }
  }
  meta?: {
    date_from: string
    date_to: string
    page: number
    per_page: number
    total_pages: number
    preferences?: {
      columns: string[]
    }
  }
}

// Batch response structure
export interface LoggedInUsersBatchResponse {
  success: boolean
  items: {
    logged_in_users?: LoggedInUsersTableResult
    logged_in_trends?: TrafficTrendsChartResult
    anonymous_trends?: TrafficTrendsChartResult
  }
  errors?: Record<string, { code: string; message: string }>
  skipped?: string[]
  meta?: {
    preferences?: Record<string, unknown>
  }
}

export interface GetLoggedInUsersBatchParams {
  // Data table params
  page: number
  per_page: number
  order_by: string
  order: 'asc' | 'desc'
  // Date ranges
  date_from: string
  date_to: string
  previous_date_from?: string
  previous_date_to?: string
  // Chart params
  group_by?: 'date' | 'week' | 'month'
  // Common params
  filters?: Filter[]
  context?: string
  columns?: string[]
}

export const getLoggedInUsersBatchQueryOptions = ({
  page,
  per_page,
  order_by,
  order,
  date_from,
  date_to,
  previous_date_from,
  previous_date_to,
  group_by = 'date',
  filters = [],
  context,
  columns,
}: GetLoggedInUsersBatchParams) => {
  // Map frontend column name to API column name
  const apiOrderBy = columnMapping[order_by] || order_by
  // Transform UI filters to API format
  const apiFilters = transformFiltersToApi(filters)
  // Check if compare dates are provided
  const hasCompare = !!(previous_date_from && previous_date_to)

  // Convert object filters to array format for batch query filters
  // Batch queries expect filters as array: [{ key, operator, value }]
  const filtersToArray = (filters: ApiFilters): Array<{ key: string; operator: string; value: string | string[] }> => {
    return Object.entries(filters).map(([key, operatorValue]) => {
      const [operator, value] = Object.entries(operatorValue)[0]
      return { key, operator, value }
    })
  }

  // Create logged-in filters array (ensure logged_in is always '1' for chart)
  const loggedInFiltersArray = [
    ...filtersToArray(apiFilters).filter((f) => f.key !== 'logged_in'),
    { key: 'logged_in', operator: 'is', value: '1' },
  ]

  // Create anonymous filters array (logged_in is '0')
  const anonymousFiltersArray = [
    ...filtersToArray(apiFilters).filter((f) => f.key !== 'logged_in'),
    { key: 'logged_in', operator: 'is', value: '0' },
  ]

  return queryOptions({
    queryKey: [
      'logged-in-users-batch',
      page,
      per_page,
      order_by,
      order,
      date_from,
      date_to,
      previous_date_from,
      previous_date_to,
      group_by,
      apiFilters,
      context,
      columns,
    ],
    queryFn: () =>
      clientRequest.post<LoggedInUsersBatchResponse>(
        '',
        {
          date_from,
          date_to,
          compare: hasCompare,
          ...(hasCompare && {
            previous_date_from,
            previous_date_to,
          }),
          queries: [
            // Data table query
            {
              id: 'logged_in_users',
              sources: ['visitors'],
              group_by: ['visitor'],
              columns: columns || [
                'visitor_id',
                'visitor_hash',
                'last_visit',
                'total_views',
                'total_sessions',
                'country_code',
                'country_name',
                'region_name',
                'city_name',
                'os_name',
                'browser_name',
                'browser_version',
                'device_type_name',
                'user_id',
                'user_login',
                'user_email',
                'user_role',
                'ip_address',
                'referrer_domain',
                'referrer_channel',
                'entry_page',
                'entry_page_title',
              ],
              ...(Object.keys(apiFilters).length > 0 && { filters: apiFilters }),
              // Override date range for data table
              page,
              per_page,
              order_by: apiOrderBy,
              order: order.toUpperCase(),
              format: 'table',
              ...(context && { context }),
            },
            // Logged-in users traffic trends
            {
              id: 'logged_in_trends',
              sources: ['visitors'],
              group_by: [group_by],
              filters: loggedInFiltersArray,
              format: 'chart',
            },
            // Anonymous visitors traffic trends
            {
              id: 'anonymous_trends',
              sources: ['visitors'],
              group_by: [group_by],
              filters: anonymousFiltersArray,
              format: 'chart',
            },
          ],
        },
        {
          params: {
            action: WordPress.getInstance().getAnalyticsAction(),
          },
        }
      ),
    staleTime: 5 * 60 * 1000, // 5 minutes
  })
}
