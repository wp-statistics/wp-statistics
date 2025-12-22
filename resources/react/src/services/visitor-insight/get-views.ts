import { queryOptions } from '@tanstack/react-query'

import { clientRequest } from '@/lib/client-request'

export interface ViewRecord {
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
  device_type_name: string
  user_id: number | null
  user_login: string | null
  referrer_domain: string | null
  referrer_channel: string
  entry_page: string
  entry_page_title: string
}

export interface GetViewsResponse {
  success: boolean
  data: {
    rows: ViewRecord[]
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
  }
}

// API filter value types
export type ApiFilterValue =
  | string
  | number
  | boolean
  | { is: string | number | boolean }
  | { is_not: string | number }
  | { in: (string | number)[] }
  | { not_in: (string | number)[] }
  | { contains: string }
  | { starts_with: string }
  | { gt: number }
  | { gte: number }
  | { lt: number }
  | { lte: number }
  | { between: [number, number] }

export type ApiFilters = Record<string, ApiFilterValue>

export interface GetViewsParams {
  page: number
  per_page: number
  order_by: string
  order: 'asc' | 'desc'
  date_from: string
  date_to: string
  filters?: ApiFilters
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

export const getViewsQueryOptions = ({ page, per_page, order_by, order, date_from, date_to, filters }: GetViewsParams) => {
  // Map frontend column name to API column name
  const apiOrderBy = columnMapping[order_by] || order_by

  // Create a stable string representation of filters for queryKey
  const filtersKey = filters ? JSON.stringify(filters) : ''

  return queryOptions({
    queryKey: ['views', page, per_page, apiOrderBy, order, date_from, date_to, filtersKey],
    queryFn: () =>
      clientRequest.post<GetViewsResponse>(
        '',
        {
          sources: ['visitors'],
          group_by: ['visitor'],
          columns: [
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
            'device_type_name',
            'user_id',
            'user_login',
            'referrer_domain',
            'referrer_channel',
            'entry_page',
            'entry_page_title',
          ],
          date_from,
          date_to,
          page,
          per_page,
          order_by: apiOrderBy,
          order: order.toUpperCase(),
          format: 'table',
          context: 'views_page_table',
          ...(filters && Object.keys(filters).length > 0 && { filters }),
        },
        {
          params: {
            action: 'wp_statistics_analytics',
          },
        }
      ),
  })
}
