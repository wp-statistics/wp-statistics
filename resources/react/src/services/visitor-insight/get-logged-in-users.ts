import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { type ApiFilters,transformFiltersToApi } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

export interface LoggedInUser {
  visitor_id: number
  visitor_hash: string
  last_visit: string
  total_views: number
  total_sessions: number
  country_code: string
  country_name: string
  region_name: string
  city_name: string
  os_name: string
  browser_name: string
  browser_version: string | null
  device_type_name: string
  user_id: number
  user_login: string
  user_email: string | null
  user_role: string | null
  ip_address: string
  referrer_domain: string | null
  referrer_channel: string
  entry_page: string
  entry_page_title: string
  entry_page_type?: string
  entry_page_wp_id?: number | null
}

export interface GetLoggedInUsersResponse {
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

// Re-export ApiFilters type for backward compatibility
export type { ApiFilters }

export interface GetLoggedInUsersParams {
  page: number
  per_page: number
  order_by: string
  order: 'asc' | 'desc'
  date_from: string
  date_to: string
  previous_date_from?: string
  previous_date_to?: string
  filters?: Filter[]
  context?: string
  columns?: string[]
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

// Default columns when no specific columns are provided
const DEFAULT_COLUMNS = [
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
  'entry_page_type',
  'entry_page_wp_id',
]

export const getLoggedInUsersQueryOptions = ({
  page,
  per_page,
  order_by,
  order,
  date_from,
  date_to,
  previous_date_from,
  previous_date_to,
  filters = [],
  context,
  columns,
}: GetLoggedInUsersParams) => {
  // Map frontend column name to API column name
  const apiOrderBy = columnMapping[order_by] || order_by
  // Transform UI filters to API format
  const apiFilters = transformFiltersToApi(filters)
  // Check if compare dates are provided (must be boolean, not the date value)
  const hasCompare = !!(previous_date_from && previous_date_to)
  // Use provided columns or default to all columns
  const apiColumns = columns && columns.length > 0 ? columns : DEFAULT_COLUMNS

  return queryOptions({
    queryKey: [
      'logged-in-users',
      page,
      per_page,
      order_by,
      order,
      date_from,
      date_to,
      previous_date_from,
      previous_date_to,
      apiFilters,
      context,
      apiColumns,
    ],
    queryFn: () =>
      clientRequest.post<GetLoggedInUsersResponse>(
        '',
        {
          sources: ['visitors'],
          group_by: ['visitor'],
          columns: apiColumns,
          ...(Object.keys(apiFilters).length > 0 && { filters: apiFilters }),
          date_from,
          date_to,
          compare: hasCompare,
          ...(hasCompare && {
            previous_date_from,
            previous_date_to,
          }),
          page,
          per_page,
          order_by: apiOrderBy,
          order: order.toUpperCase(),
          format: 'table',
          ...(context && { context }),
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
