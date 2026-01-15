import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { type ApiFilters,transformFiltersToApi } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

export type { ApiFilters }

// Metric value with current/previous structure
interface MetricValue {
  current: number | string
  previous: number | string
}

// Flat format response (for metrics query)
export interface MetricsResponse {
  success: boolean
  items: Array<Record<string, string | number>>
  totals: {
    visitors: MetricValue
    views: MetricValue
  }
  meta: {
    date_from: string
    date_to: string
    cached: boolean
    cache_ttl: number
    preferences: unknown
    compare_from?: string
    compare_to?: string
  }
}

// Chart format response (for traffic trends)
export interface TrafficTrendsChartResponse {
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

// Table format row types
export interface TopCountryRow {
  country_code: string
  country_name: string
  visitors: number | string
  previous?: {
    visitors: number | string
  }
}

export interface TopReferrerRow {
  referrer_domain: string | null
  referrer_name: string | null
  referrer_channel: string | null
  visitors: number | string
  previous?: {
    visitors: number | string
  }
}

export interface DeviceTypeRow {
  device_type_name: string
  visitors: number | string
  previous?: {
    visitors: number | string
  }
}

export interface OperatingSystemRow {
  os_name: string
  visitors: number | string
  previous?: {
    visitors: number | string
  }
}

export interface BrowserRow {
  browser_name: string
  visitors: number | string
  previous?: {
    visitors: number | string
  }
}

export interface SourceCategoryRow {
  referrer_channel: string
  visitors: number | string
  previous?: {
    visitors: number | string
  }
}

export interface EntryPageRow {
  page_uri: string
  page_title: string
  page_type: string
  visitors: number | string
  previous?: {
    visitors: number | string
  }
}

// Table format response wrapper
export interface TableQueryResult<T> {
  success: boolean
  data: {
    rows: T[]
    totals?: Record<string, unknown>
  }
  meta?: {
    date_from: string
    date_to: string
    page?: number
    per_page?: number
    total_pages?: number
    total_rows?: number
    preferences?: Record<string, unknown> | null
    cached: boolean
    cache_ttl: number
    compare_from?: string
    compare_to?: string
  }
}

// Batch response structure
export interface ReferralsOverviewResponse {
  success: boolean
  items: {
    // Flat format - totals at top level
    metrics?: MetricsResponse
    metrics_top_referrer?: {
      success: boolean
      items: Array<{ referrer_name: string; visitors: number }>
      totals: Record<string, unknown>
    }
    metrics_top_country?: {
      success: boolean
      items: Array<{ country_name: string; visitors: number }>
      totals: Record<string, unknown>
    }
    metrics_top_browser?: {
      success: boolean
      items: Array<{ browser_name: string; visitors: number }>
      totals: Record<string, unknown>
    }
    metrics_top_search_engine?: {
      success: boolean
      items: Array<{ referrer_name: string; visitors: number }>
      totals: Record<string, unknown>
    }
    metrics_top_social?: {
      success: boolean
      items: Array<{ referrer_name: string; visitors: number }>
      totals: Record<string, unknown>
    }
    metrics_top_entry_page?: {
      success: boolean
      items: Array<{ page_title: string; visitors: number }>
      totals: Record<string, unknown>
    }
    // Chart format
    traffic_trends?: TrafficTrendsChartResponse
    // Table format
    top_referrers?: TableQueryResult<TopReferrerRow>
    top_countries?: TableQueryResult<TopCountryRow>
    top_operating_systems?: TableQueryResult<OperatingSystemRow>
    top_device_categories?: TableQueryResult<DeviceTypeRow>
    top_source_categories?: TableQueryResult<SourceCategoryRow>
    top_search_engines?: TableQueryResult<TopReferrerRow>
    top_social_media?: TableQueryResult<TopReferrerRow>
    top_entry_pages?: TableQueryResult<EntryPageRow>
  }
  errors?: Record<string, { code: string; message: string }>
  skipped?: string[]
  meta?: {
    preferences?: Record<string, unknown>
  }
}

export interface GetReferralsOverviewParams {
  dateFrom: string
  dateTo: string
  compareDateFrom?: string
  compareDateTo?: string
  timeframe?: 'daily' | 'weekly' | 'monthly'
  filters?: Filter[]
}

export const getReferralsOverviewQueryOptions = ({
  dateFrom,
  dateTo,
  compareDateFrom,
  compareDateTo,
  timeframe = 'daily',
  filters = [],
}: GetReferralsOverviewParams) => {
  const dateGroupBy = timeframe === 'monthly' ? 'month' : timeframe === 'weekly' ? 'week' : 'date'
  const apiFilters = transformFiltersToApi(filters)
  const hasCompare = !!(compareDateFrom && compareDateTo)

  // Base filter for all queries: only referred traffic (has referrer)
  const referredFilter = { key: 'referrer_domain', operator: 'is_not_empty', value: '' }

  return queryOptions({
    queryKey: ['referrals-overview', dateFrom, dateTo, compareDateFrom, compareDateTo, timeframe, apiFilters],
    queryFn: () =>
      clientRequest.post<ReferralsOverviewResponse>(
        '',
        {
          date_from: dateFrom,
          date_to: dateTo,
          compare: hasCompare,
          ...(hasCompare && {
            previous_date_from: compareDateFrom,
            previous_date_to: compareDateTo,
          }),
          ...(Object.keys(apiFilters).length > 0 && { filters: apiFilters }),
          queries: [
            // Metrics: Flat format for aggregate totals (referred visitors only)
            {
              id: 'metrics',
              sources: ['visitors', 'views'],
              group_by: [],
              filters: [referredFilter],
              format: 'flat',
              show_totals: true,
              compare: true,
            },
            // Top Referrer
            {
              id: 'metrics_top_referrer',
              sources: ['visitors'],
              group_by: ['referrer'],
              columns: ['referrer_name', 'visitors'],
              filters: [referredFilter],
              per_page: 1,
              order_by: 'visitors',
              order: 'DESC',
              format: 'flat',
              show_totals: false,
              compare: false,
            },
            // Top Country
            {
              id: 'metrics_top_country',
              sources: ['visitors'],
              group_by: ['country'],
              columns: ['country_name', 'visitors'],
              filters: [referredFilter],
              per_page: 1,
              order_by: 'visitors',
              order: 'DESC',
              format: 'flat',
              show_totals: false,
              compare: false,
            },
            // Top Browser
            {
              id: 'metrics_top_browser',
              sources: ['visitors'],
              group_by: ['browser'],
              columns: ['browser_name', 'visitors'],
              filters: [referredFilter],
              per_page: 1,
              order_by: 'visitors',
              order: 'DESC',
              format: 'flat',
              show_totals: false,
              compare: false,
            },
            // Top Search Engine
            {
              id: 'metrics_top_search_engine',
              sources: ['visitors'],
              group_by: ['referrer'],
              columns: ['referrer_name', 'visitors'],
              filters: [
                referredFilter,
                { key: 'referrer_channel', operator: 'contains', value: 'search' },
              ],
              per_page: 1,
              order_by: 'visitors',
              order: 'DESC',
              format: 'flat',
              show_totals: false,
              compare: false,
            },
            // Top Social Media
            {
              id: 'metrics_top_social',
              sources: ['visitors'],
              group_by: ['referrer'],
              columns: ['referrer_name', 'visitors'],
              filters: [
                referredFilter,
                { key: 'referrer_channel', operator: 'contains', value: 'social' },
              ],
              per_page: 1,
              order_by: 'visitors',
              order: 'DESC',
              format: 'flat',
              show_totals: false,
              compare: false,
            },
            // Top Entry Page
            {
              id: 'metrics_top_entry_page',
              sources: ['visitors'],
              group_by: ['entry_page'],
              columns: ['page_title', 'visitors'],
              filters: [referredFilter],
              per_page: 1,
              order_by: 'visitors',
              order: 'DESC',
              format: 'flat',
              show_totals: false,
              compare: false,
            },
            // Traffic Trends: Chart format for line chart
            {
              id: 'traffic_trends',
              sources: ['visitors', 'views'],
              group_by: [dateGroupBy],
              filters: [referredFilter],
              format: 'chart',
              show_totals: false,
              compare: true,
            },
            // Top Referrers list
            {
              id: 'top_referrers',
              sources: ['visitors'],
              group_by: ['referrer'],
              columns: ['referrer_domain', 'referrer_name', 'referrer_channel', 'visitors'],
              filters: [referredFilter],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
            // Top Countries
            {
              id: 'top_countries',
              sources: ['visitors'],
              group_by: ['country'],
              columns: ['country_code', 'country_name', 'visitors'],
              filters: [referredFilter],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
            // Top Operating Systems
            {
              id: 'top_operating_systems',
              sources: ['visitors'],
              group_by: ['os'],
              columns: ['os_name', 'visitors'],
              filters: [referredFilter],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
            // Top Device Categories
            {
              id: 'top_device_categories',
              sources: ['visitors'],
              group_by: ['device_type'],
              columns: ['device_type_name', 'visitors'],
              filters: [referredFilter],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
            // Top Source Categories (channels)
            {
              id: 'top_source_categories',
              sources: ['visitors'],
              group_by: ['referrer_channel'],
              columns: ['referrer_channel', 'visitors'],
              filters: [referredFilter],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
            // Top Search Engines
            {
              id: 'top_search_engines',
              sources: ['visitors'],
              group_by: ['referrer'],
              columns: ['referrer_domain', 'referrer_name', 'visitors'],
              filters: [
                referredFilter,
                { key: 'referrer_channel', operator: 'contains', value: 'search' },
              ],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
            // Top Social Media
            {
              id: 'top_social_media',
              sources: ['visitors'],
              group_by: ['referrer'],
              columns: ['referrer_domain', 'referrer_name', 'visitors'],
              filters: [
                referredFilter,
                { key: 'referrer_channel', operator: 'contains', value: 'social' },
              ],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
            // Top Entry Pages
            {
              id: 'top_entry_pages',
              sources: ['visitors'],
              group_by: ['entry_page'],
              columns: ['page_uri', 'page_title', 'page_type', 'visitors'],
              filters: [referredFilter],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
          ],
        },
        {
          params: {
            action: WordPress.getInstance().getAnalyticsAction(),
          },
        }
      ),
  })
}
