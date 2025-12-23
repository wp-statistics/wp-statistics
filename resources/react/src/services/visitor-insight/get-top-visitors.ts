import { queryOptions } from '@tanstack/react-query'

import { clientRequest } from '@/lib/client-request'

export interface TopVisitorRecord {
  visitor_id: number
  visitor_hash: string
  ip_address: string | null
  user_id: number | null
  user_login: string | null
  total_views: number
  total_sessions: number
  last_visit: string
  first_visit: string
  bounce_rate: number | null
  avg_session_duration: number | null
  pages_per_session: number | null
  visitor_status: 'new' | 'returning' | null
  country_code: string | null
  country_name: string | null
  region_name: string | null
  city_name: string | null
  os_name: string | null
  browser_name: string | null
  device_type_name: string | null
  referrer_domain: string | null
  referrer_channel: string | null
  entry_page: string | null
  entry_page_title: string | null
  exit_page: string | null
  exit_page_title: string | null
}

export interface GetTopVisitorsResponse {
  success: boolean
  data: {
    rows: TopVisitorRecord[]
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
  }
}

export interface GetTopVisitorsParams {
  page: number
  per_page: number
  order_by: string
  order: 'asc' | 'desc'
  date_from: string
  date_to: string
}

// Map frontend column names to API column names
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
  firstVisit: 'first_visit',
}

export const getTopVisitorsQueryOptions = ({
  page,
  per_page,
  order_by,
  order,
  date_from,
  date_to,
}: GetTopVisitorsParams) => {
  // Map frontend column name to API column name
  const apiOrderBy = columnMapping[order_by] || order_by

  return queryOptions({
    queryKey: ['top-visitors', page, per_page, apiOrderBy, order, date_from, date_to],
    queryFn: () =>
      clientRequest.post<GetTopVisitorsResponse>(
        '',
        {
          sources: ['avg_session_duration', 'bounce_rate', 'pages_per_session', 'visitor_status'],
          group_by: ['visitor'],
          columns: [
            'visitor_id',
            'visitor_hash',
            'ip_address',
            'user_id',
            'user_login',
            'total_views',
            'total_sessions',
            'last_visit',
            'bounce_rate',
            'avg_session_duration',
            'pages_per_session',
            'visitor_status',
            'country_code',
            'country_name',
            'region_name',
            'city_name',
            'os_name',
            'browser_name',
            'device_type_name',
            'referrer_domain',
            'referrer_channel',
            'entry_page',
            'entry_page_title',
            'exit_page',
            'exit_page_title',
          ],
          date_from,
          date_to,
          page,
          per_page,
          order_by: apiOrderBy,
          order: order.toUpperCase(),
          format: 'table',
          show_totals: false,
        },
        {
          params: {
            action: 'wp_statistics_analytics',
          },
        }
      ),
  })
}
