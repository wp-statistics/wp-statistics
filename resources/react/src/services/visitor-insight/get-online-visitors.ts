import { queryOptions } from '@tanstack/react-query'

import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

export interface OnlineVisitor {
  visitor_id: number
  visitor_hash: string
  ip_address: string
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
  user_id: number | null
  user_login: string | null
  user_email: string | null
  user_role: string | null
  referrer_domain: string | null
  referrer_channel: string
  entry_page: string
  entry_page_type?: string | null
  entry_page_wp_id?: number | null
  visitors?: number
}

export interface GetOnlineVisitorsResponse {
  success: boolean
  data: {
    rows: OnlineVisitor[]
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
    total_rows: number
    preferences?: {
      columns: string[]
    }
  }
}

export interface GetOnlineVisitorsParams {
  page?: number
  per_page?: number
  order_by?: string
  order?: 'asc' | 'desc'
  // Time range in minutes (default 5 minutes for "online" visitors)
  timeRangeMinutes?: number
  // Context for user preferences
  context?: string
  // Dynamic columns for query optimization
  columns?: string[]
}

// Map frontend column names to API column names
const columnMapping: Record<string, string> = {
  visitorInfo: 'visitor_id',
  onlineFor: 'total_sessions',
  page: 'entry_page',
  totalViews: 'total_views',
  entryPage: 'entry_page',
  referrer: 'referrer_domain',
  lastVisit: 'last_visit',
}

// Default columns when no specific columns are provided
const DEFAULT_COLUMNS = [
  'visitor_id',
  'visitor_hash',
  'ip_address',
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
  'referrer_domain',
  'referrer_channel',
  'entry_page',
  'entry_page_type',
  'entry_page_wp_id',
]

// Format dates to ISO string (YYYY-MM-DDTHH:mm:ss)
const formatDate = (date: Date) => {
  return date.toISOString().slice(0, 19)
}

export const getOnlineVisitorsQueryOptions = ({
  page = 1,
  per_page = 50,
  order_by = 'lastVisit',
  order = 'desc',
  timeRangeMinutes = 5,
  context,
  columns,
}: GetOnlineVisitorsParams = {}) => {
  // Map frontend column name to API column name
  const apiOrderBy = columnMapping[order_by] || order_by
  // Use provided columns or default to all columns
  const apiColumns = columns && columns.length > 0 ? columns : DEFAULT_COLUMNS

  return queryOptions({
    queryKey: ['online-visitors', page, per_page, apiOrderBy, order, timeRangeMinutes, context, apiColumns],
    queryFn: () => {
      // Calculate date range INSIDE queryFn so it's fresh on each actual fetch
      // This prevents StrictMode double-mount from creating different request bodies
      const now = new Date()
      const dateFrom = new Date(now.getTime() - timeRangeMinutes * 60 * 1000)
      const dateTo = now

      return clientRequest.post<GetOnlineVisitorsResponse>(
        '',
        {
          sources: ['visitors'],
          group_by: ['online_visitor'],
          columns: apiColumns,
          date_from: formatDate(dateFrom),
          date_to: formatDate(dateTo),
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
      )
    },
    staleTime: 15 * 1000, // 15 seconds
    refetchOnWindowFocus: true,
    refetchInterval: 30 * 1000,
  })
}
