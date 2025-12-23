import { queryOptions } from '@tanstack/react-query'

import { clientRequest } from '@/lib/client-request'

// Response types for each query in the batch
export interface MetricsData {
  rows: Array<{
    visitors: number
    views: number
    sessions?: number
    avg_session_duration?: number
    pages_per_session?: number
    bounce_rate?: number
  }>
  totals?: {
    visitors: number
    views: number
    sessions?: number
    avg_session_duration?: number
    pages_per_session?: number
    bounce_rate?: number
    previous?: {
      visitors: number
      views: number
      sessions?: number
      avg_session_duration?: number
      pages_per_session?: number
      bounce_rate?: number
    }
  }
}

export interface TrafficTrendsData {
  rows: Array<{
    date: string
    visitors: number
    views: number
    previous?: {
      visitors: number
      views: number
    }
  }>
  totals?: {
    visitors: number
    views: number
  }
}

export interface TopCountriesData {
  rows: Array<{
    country_code: string
    country_name: string
    visitors: number
    previous?: {
      visitors: number
    }
  }>
  total: number
}

export interface DeviceTypeData {
  rows: Array<{
    device_type_name: string
    visitors: number
    previous?: {
      visitors: number
    }
  }>
  total: number
}

export interface OperatingSystemsData {
  rows: Array<{
    os_name: string
    visitors: number
    previous?: {
      visitors: number
    }
  }>
  total: number
}

export interface TopVisitorsData {
  rows: Array<{
    visitor_id: number
    visitor_hash: string
    ip_address: string | null
    user_id: number | null
    user_login: string | null
    total_views: number
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
  }>
  total: number
}

export interface QueryResult<T> {
  success: boolean
  data: T
  meta?: {
    date_from: string
    date_to: string
    page?: number
    per_page?: number
    total_pages?: number
    preferences?: Record<string, unknown> | null
  }
}

export interface VisitorsOverviewBatchResponse {
  success: boolean
  items: {
    metrics?: QueryResult<MetricsData>
    traffic_trends?: QueryResult<TrafficTrendsData>
    top_countries?: QueryResult<TopCountriesData>
    device_type?: QueryResult<DeviceTypeData>
    operating_systems?: QueryResult<OperatingSystemsData>
    top_visitors?: QueryResult<TopVisitorsData>
  }
  errors?: Record<string, { code: string; message: string }>
  skipped?: string[]
  meta?: {
    preferences?: Record<string, unknown>
  }
}

export interface GetVisitorsOverviewBatchParams {
  dateFrom: string
  dateTo: string
  timeframe?: 'daily' | 'weekly' | 'monthly'
  compare?: boolean
}

export const getVisitorsOverviewBatchQueryOptions = ({
  dateFrom,
  dateTo,
  timeframe = 'daily',
  compare = true,
}: GetVisitorsOverviewBatchParams) => {
  // Determine the appropriate date group_by based on timeframe
  const dateGroupBy = timeframe === 'monthly' ? 'month' : timeframe === 'weekly' ? 'week' : 'date'

  return queryOptions({
    queryKey: ['visitors-overview-batch', dateFrom, dateTo, timeframe, compare],
    queryFn: () =>
      clientRequest.post<VisitorsOverviewBatchResponse>(
        '',
        {
          date_from: dateFrom,
          date_to: dateTo,
          compare,
          queries: [
            {
              id: 'metrics',
              sources: ['visitors', 'views', 'sessions', 'avg_session_duration', 'pages_per_session', 'bounce_rate'],
              format: 'flat',
              show_totals: true,
            },
            {
              id: 'traffic_trends',
              sources: ['visitors', 'views'],
              group_by: [dateGroupBy],
              format: 'chart',
              show_totals: false,
            },
            {
              id: 'top_countries',
              sources: ['visitors'],
              group_by: ['country'],
              columns: ['country_code', 'country_name', 'visitors'],
              per_page: 10,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: false,
            },
            {
              id: 'device_type',
              sources: ['visitors'],
              group_by: ['device_type'],
              columns: ['device_type_name', 'visitors'],
              per_page: 10,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: false,
            },
            {
              id: 'operating_systems',
              sources: ['visitors'],
              group_by: ['platform'],
              columns: ['os_name', 'visitors'],
              per_page: 10,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: false,
            },
            {
              id: 'top_visitors',
              sources: ['visitors'],
              group_by: ['visitor'],
              columns: [
                'visitor_id',
                'visitor_hash',
                'ip_address',
                'user_id',
                'user_login',
                'total_views',
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
              per_page: 10,
              order_by: 'total_views',
              order: 'DESC',
              format: 'table',
              show_totals: false,
            },
          ],
        },
        {
          params: {
            action: 'wp_statistics_analytics',
          },
        }
      ),
  })
}
