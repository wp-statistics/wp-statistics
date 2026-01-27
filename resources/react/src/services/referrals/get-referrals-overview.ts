import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { transformFiltersToApi } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

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
  previousLabels?: string[]
  datasets: Array<{
    key: string
    label: string
    data: (number | string)[]
    comparison?: boolean
  }>
  meta?: Record<string, unknown>
}

// Top referrer row
export interface TopReferrerRow {
  referrer_domain: string | null
  referrer_name: string | null
  referrer_channel: string | null
  visitors: number | string
  previous?: {
    visitors: number | string
  }
}

// Source category row
export interface SourceCategoryRow {
  referrer_channel: string
  visitors: number | string
  previous?: { visitors: number | string }
}

// Search engine row
export interface SearchEngineRow {
  referrer_name: string | null
  referrer_domain: string | null
  visitors: number | string
  previous?: { visitors: number | string }
}

// Social media row (same as SearchEngineRow)
export type SocialMediaRow = SearchEngineRow

// Country row
export interface CountryRow {
  country_code: string
  country_name: string
  visitors: number | string
  previous?: { visitors: number | string }
}

// Operating system row
export interface OperatingSystemRow {
  os_name: string
  visitors: number | string
  previous?: { visitors: number | string }
}

// Device category row
export interface DeviceCategoryRow {
  device_type_name: string
  visitors: number | string
  previous?: { visitors: number | string }
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
    // Referred visitors count (flat format)
    metrics?: MetricsResponse
    // Top referrer name (flat format)
    metrics_top_referrer?: {
      success: boolean
      items: Array<{ referrer_name: string; referrer_domain: string; visitors: number }>
      totals: Record<string, unknown>
    }
    // Top search engine name (flat format)
    metrics_top_search_engine?: {
      success: boolean
      items: Array<{ referrer_name: string; visitors: number }>
      totals: Record<string, unknown>
    }
    // Top social media name (flat format)
    metrics_top_social?: {
      success: boolean
      items: Array<{ referrer_name: string; visitors: number }>
      totals: Record<string, unknown>
    }
    // Top entry page title (flat format)
    metrics_top_entry_page?: {
      success: boolean
      items: Array<{ page_title: string; page_uri: string; visitors: number }>
      totals: Record<string, unknown>
    }
    // Traffic trends (chart format)
    traffic_trends?: TrafficTrendsChartResponse
    // Top referrers list (table format)
    top_referrers?: TableQueryResult<TopReferrerRow>
    // Top source categories (table format)
    top_source_categories?: TableQueryResult<SourceCategoryRow>
    // Top search engines (table format)
    top_search_engines?: TableQueryResult<SearchEngineRow>
    // Top social media (table format)
    top_social_media?: TableQueryResult<SocialMediaRow>
    // Top countries (table format)
    top_countries?: TableQueryResult<CountryRow>
    // Top operating systems (table format)
    top_operating_systems?: TableQueryResult<OperatingSystemRow>
    // Top device categories (table format)
    top_device_categories?: TableQueryResult<DeviceCategoryRow>
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
  // Determine the appropriate date group_by based on timeframe
  const dateGroupBy = timeframe === 'monthly' ? 'month' : timeframe === 'weekly' ? 'week' : 'date'
  // Transform UI filters to API format
  const apiFilters = transformFiltersToApi(filters)
  // Check if compare dates are provided (must be boolean, not the date value)
  const hasCompare = !!(compareDateFrom && compareDateTo)

  // Filter to exclude direct traffic
  const notDirectFilter = { key: 'referrer_channel', operator: 'is_not', value: 'direct' }

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
            // Referred visitors count (excludes direct traffic)
            {
              id: 'metrics',
              sources: ['visitors'],
              group_by: [],
              filters: [notDirectFilter],
              format: 'flat',
              show_totals: true,
              compare: true,
            },
            // Top referrer name
            {
              id: 'metrics_top_referrer',
              sources: ['visitors'],
              group_by: ['referrer'],
              columns: ['referrer_name', 'referrer_domain', 'visitors'],
              filters: [notDirectFilter],
              per_page: 1,
              order_by: 'visitors',
              order: 'DESC',
              format: 'flat',
              show_totals: false,
              compare: false,
            },
            // Top search engine
            {
              id: 'metrics_top_search_engine',
              sources: ['visitors'],
              group_by: ['referrer'],
              columns: ['referrer_name', 'visitors'],
              filters: [{ key: 'referrer_channel', operator: 'is', value: 'search' }],
              per_page: 1,
              order_by: 'visitors',
              order: 'DESC',
              format: 'flat',
              show_totals: false,
              compare: false,
            },
            // Top social media
            {
              id: 'metrics_top_social',
              sources: ['visitors'],
              group_by: ['referrer'],
              columns: ['referrer_name', 'visitors'],
              filters: [{ key: 'referrer_channel', operator: 'is', value: 'social' }],
              per_page: 1,
              order_by: 'visitors',
              order: 'DESC',
              format: 'flat',
              show_totals: false,
              compare: false,
            },
            // Top entry page
            {
              id: 'metrics_top_entry_page',
              sources: ['visitors'],
              group_by: ['entry_page'],
              columns: ['page_title', 'page_uri', 'visitors'],
              filters: [notDirectFilter],
              per_page: 1,
              order_by: 'visitors',
              order: 'DESC',
              format: 'flat',
              show_totals: false,
              compare: false,
            },
            // Traffic trends chart (excludes direct traffic)
            {
              id: 'traffic_trends',
              sources: ['visitors', 'views'],
              group_by: [dateGroupBy],
              filters: [notDirectFilter],
              format: 'chart',
              show_totals: false,
              compare: true,
            },
            // Top referrers list (excludes direct traffic)
            {
              id: 'top_referrers',
              sources: ['visitors'],
              group_by: ['referrer'],
              columns: ['referrer_domain', 'referrer_name', 'referrer_channel', 'visitors'],
              filters: [notDirectFilter],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
            // Top source categories (grouped by referrer_channel, excludes direct)
            {
              id: 'top_source_categories',
              sources: ['visitors'],
              group_by: ['referrer_channel'],
              columns: ['referrer_channel', 'visitors'],
              filters: [notDirectFilter],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
            // Top search engines (filtered by channel=search)
            {
              id: 'top_search_engines',
              sources: ['visitors'],
              group_by: ['referrer'],
              columns: ['referrer_name', 'referrer_domain', 'visitors'],
              filters: [{ key: 'referrer_channel', operator: 'is', value: 'search' }],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
            // Top social media (filtered by channel=social)
            {
              id: 'top_social_media',
              sources: ['visitors'],
              group_by: ['referrer'],
              columns: ['referrer_name', 'referrer_domain', 'visitors'],
              filters: [{ key: 'referrer_channel', operator: 'is', value: 'social' }],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
            // Top countries (excludes direct)
            {
              id: 'top_countries',
              sources: ['visitors'],
              group_by: ['country'],
              columns: ['country_code', 'country_name', 'visitors'],
              filters: [notDirectFilter],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
            // Top operating systems (excludes direct)
            {
              id: 'top_operating_systems',
              sources: ['visitors'],
              group_by: ['os'],
              columns: ['os_name', 'visitors'],
              filters: [notDirectFilter],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
            // Top device categories (excludes direct)
            {
              id: 'top_device_categories',
              sources: ['visitors'],
              group_by: ['device_type'],
              columns: ['device_type_name', 'visitors'],
              filters: [notDirectFilter],
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
