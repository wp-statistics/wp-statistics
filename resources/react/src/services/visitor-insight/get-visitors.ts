import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { type ApiFilters,transformFiltersToApi } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

export interface VisitorRecord {
  visitor_id: number
  visitor_hash: string
  ip_address: string | null
  user_id: number | null
  user_login: string | null
  user_email: string | null
  user_role: string | null
  last_visit: string
  country_code: string | null
  country_name: string | null
  region_name: string | null
  city_name: string | null
  os_name: string | null
  browser_name: string | null
  browser_version: string | null
  device_type_name: string | null
  referrer_domain: string | null
  referrer_channel: string | null
  entry_page: string | null
  entry_page_title: string | null
  entry_page_type?: string | null
  entry_page_wp_id?: number | null
  entry_page_resource_id?: number | null
  exit_page: string | null
  exit_page_title: string | null
  exit_page_type?: string | null
  exit_page_wp_id?: number | null
  exit_page_resource_id?: number | null
  total_views: number
  total_sessions: number
  avg_session_duration: number | null
  pages_per_session: number | null
  bounce_rate: number | null
  visitor_status: 'new' | 'returning' | null
  first_visit: string | null
}

export interface GetVisitorsResponse {
  success: boolean
  data: {
    rows: VisitorRecord[]
    total: number
  }
  meta?: {
    date_from: string
    date_to: string
    page: number
    per_page: number
    total_pages: number
    total_rows: number
    preferences?: {
      columns: string[] // Array of visible column IDs in their display order
      updated_at: string
    }
  }
}

// Re-export ApiFilters type for backward compatibility
export type { ApiFilters }

export interface GetVisitorsParams {
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

// Map frontend column names to API column names (for sorting)
const columnMapping: Record<string, string> = {
  lastVisit: 'last_visit',
  visitorInfo: 'visitor_id',
  totalViews: 'total_views',
  totalSessions: 'total_sessions',
  sessionDuration: 'avg_session_duration',
  viewsPerSession: 'pages_per_session',
  bounceRate: 'bounce_rate',
  entryPage: 'entry_page',
  exitPage: 'exit_page',
  referrer: 'referrer_domain',
  visitorStatus: 'visitor_status',
}

// Default columns when no specific columns are provided
const DEFAULT_COLUMNS = [
  'visitor_id',
  'visitor_hash',
  'ip_address',
  'user_id',
  'user_login',
  'user_email',
  'user_role',
  'last_visit',
  'first_visit',
  'country_code',
  'country_name',
  'region_name',
  'city_name',
  'os_name',
  'browser_name',
  'browser_version',
  'device_type_name',
  'referrer_domain',
  'referrer_channel',
  'entry_page',
  'entry_page_title',
  'entry_page_type',
  'entry_page_wp_id',
  'exit_page',
  'exit_page_title',
  'exit_page_type',
  'exit_page_wp_id',
  'total_views',
  'total_sessions',
  'avg_session_duration',
  'pages_per_session',
  'bounce_rate',
  'visitor_status',
]

export const getVisitorsQueryOptions = ({
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
}: GetVisitorsParams) => {
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
      'visitors',
      page,
      per_page,
      apiOrderBy,
      order,
      date_from,
      date_to,
      previous_date_from,
      previous_date_to,
      apiFilters,
      context,
      apiColumns,
      hasCompare,
    ],
    queryFn: () =>
      clientRequest.post<GetVisitorsResponse>(
        '',
        {
          sources: ['visitors', 'avg_session_duration', 'bounce_rate', 'pages_per_session', 'visitor_status'],
          group_by: ['visitor'],
          columns: apiColumns,
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
          show_totals: false,
          ...(Object.keys(apiFilters).length > 0 && { filters: apiFilters }),
          ...(context && { context }),
        },
        {
          params: {
            action: WordPress.getInstance().getAnalyticsAction(),
          },
        }
      ),
  })
}
