import { queryOptions } from '@tanstack/react-query'

import { clientRequest } from '@/lib/client-request'

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
  device_type_name: string
  user_id: number
  user_login: string
  ip_address: string
  referrer_domain: string | null
  referrer_channel: string
  entry_page: string
  entry_page_title: string
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
  }
}

export interface GetLoggedInUsersParams {
  page: number
  per_page: number
  order_by: string
  order: 'asc' | 'desc'
  date_from: string
  date_to: string
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

export const getLoggedInUsersQueryOptions = ({
  page,
  per_page,
  order_by,
  order,
  date_from,
  date_to,
}: GetLoggedInUsersParams) => {
  // Map frontend column name to API column name
  const apiOrderBy = columnMapping[order_by] || order_by

  return queryOptions({
    queryKey: ['logged-in-users', page, per_page, order_by, order, date_from, date_to],
    queryFn: () =>
      clientRequest.post<GetLoggedInUsersResponse>(
        '',
        {
          sources: ['visitors'],
          group_by: ['visitor'],
          columns: [
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
            'device_type_name',
            'user_id',
            'user_login',
            'ip_address',
            'referrer_domain',
            'referrer_channel',
            'entry_page',
            'entry_page_title',
          ],
          filters: {
            logged_in: {
              is: '1',
            },
          },
          date_from,
          date_to,
          page,
          per_page,
          order_by: apiOrderBy,
          order: order.toUpperCase(),
          format: 'table',
          context: 'logged_in_users_page_table',
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
