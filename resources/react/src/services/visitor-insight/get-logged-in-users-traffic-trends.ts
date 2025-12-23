import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { clientRequest } from '@/lib/client-request'

interface TrafficTrendRow {
  date?: string
  week?: string
  month?: string
  visitors: number | string
  previous?: {
    visitors: number | string
  }
}

export interface LoggedInUsersTrafficTrendsResponse {
  success: boolean
  data: {
    rows: TrafficTrendRow[]
  }
  meta?: {
    date_from: string
    date_to: string
  }
}

// API filter format: { filter_key: { operator: value } }
type ApiFilters = Record<string, Record<string, string | string[]>>

export interface GetLoggedInUsersTrafficTrendsParams {
  date_from: string
  date_to: string
  group_by?: 'date' | 'week' | 'month'
  filters?: Filter[]
}

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

// Query for logged-in users traffic trends
export const getLoggedInUsersTrafficTrendsQueryOptions = ({
  date_from,
  date_to,
  group_by = 'date',
  filters = [],
}: GetLoggedInUsersTrafficTrendsParams) => {
  const apiFilters = transformFiltersToApi(filters)

  return queryOptions({
    queryKey: ['logged-in-users-traffic-trends', date_from, date_to, group_by, apiFilters],
    queryFn: () =>
      clientRequest.post<LoggedInUsersTrafficTrendsResponse>(
        '',
        {
          sources: ['visitors'],
          group_by: [group_by],
          columns: [group_by, 'visitors'],
          ...(Object.keys(apiFilters).length > 0 && { filters: apiFilters }),
          date_from,
          date_to,
          format: 'table',
          compare: true,
          show_totals: false,
          per_page: 100,
          order_by: group_by,
          order: 'ASC',
          context: 'logged_in_users_traffic_trends',
        },
        {
          params: {
            action: 'wp_statistics_analytics',
          },
        }
      ),
    staleTime: 5 * 60 * 1000, // 5 minutes
  })
}

// Query for anonymous visitors traffic trends
export const getAnonymousVisitorsTrafficTrendsQueryOptions = ({
  date_from,
  date_to,
  group_by = 'date',
  filters = [],
}: GetLoggedInUsersTrafficTrendsParams) => {
  // For anonymous visitors, we need to override the logged_in filter
  const userFilters = transformFiltersToApi(filters)
  // Replace logged_in filter with anonymous (is: '0')
  const apiFilters = {
    ...userFilters,
    logged_in: { is: '0' },
  }

  return queryOptions({
    queryKey: ['anonymous-visitors-traffic-trends', date_from, date_to, group_by, apiFilters],
    queryFn: () =>
      clientRequest.post<LoggedInUsersTrafficTrendsResponse>(
        '',
        {
          sources: ['visitors'],
          group_by: [group_by],
          columns: [group_by, 'visitors'],
          filters: apiFilters,
          date_from,
          date_to,
          format: 'table',
          compare: true,
          show_totals: false,
          per_page: 100,
          order_by: group_by,
          order: 'ASC',
          context: 'anonymous_visitors_traffic_trends',
        },
        {
          params: {
            action: 'wp_statistics_analytics',
          },
        }
      ),
    staleTime: 5 * 60 * 1000, // 5 minutes
  })
}
