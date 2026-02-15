import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { transformFiltersToApi } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import type { FixedDatePeriod } from '@/lib/fixed-date-ranges'
import { WordPress } from '@/lib/wordpress'
import {
  extractTrafficSummaryData,
  type TrafficSummaryPeriodData,
  type TrafficSummaryPeriodResponse,
} from '@/services/content-analytics/get-single-content'

import type {
  BrowserRow,
  DeviceTypeRow,
  OperatingSystemRow,
  TableQueryResult,
  TopCountryRow,
  TopReferrersResponse,
  TrafficTrendsChartResponse,
} from './get-content-overview'

// Metric value with current/previous structure
interface MetricValue {
  current: number | string
  previous?: number | string
}

// Totals structure for flat format (no comments for URL pages)
interface UrlMetricsTotals {
  visitors?: MetricValue
  views?: MetricValue
  bounce_rate?: MetricValue
  avg_time_on_page?: MetricValue
  entry_page?: MetricValue
  exit_page?: MetricValue
  exit_rate?: MetricValue
}

// Single URL metrics response (flat format)
export interface SingleUrlMetricsResponse {
  success: boolean
  items: Array<Record<string, string | number>>
  totals: UrlMetricsTotals
  meta: {
    date_from: string
    date_to: string
    cached: boolean
    cache_ttl: number
    compare_from?: string
    compare_to?: string
  }
}

// URL info row from table query
export interface UrlInfoRow {
  page_uri: string
  page_title: string
  page_wp_id: number | null
  page_type?: string
  resource_id?: number | string
  permalink?: string | null
}

// URL info response (table format)
export interface UrlInfoResponse {
  success: boolean
  data: {
    rows: UrlInfoRow[]
  }
}

// Batch response structure for single URL
export interface SingleUrlResponse {
  success: boolean
  items: {
    url_metrics?: SingleUrlMetricsResponse
    url_info?: UrlInfoResponse
    traffic_trends?: TrafficTrendsChartResponse
    top_referrers?: TopReferrersResponse
    top_search_engines?: TopReferrersResponse
    top_countries?: TableQueryResult<TopCountryRow>
    top_browsers?: TableQueryResult<BrowserRow>
    top_operating_systems?: TableQueryResult<OperatingSystemRow>
    top_device_categories?: TableQueryResult<DeviceTypeRow>
  }
  errors?: Record<string, { code: string; message: string }>
  skipped?: string[]
  meta?: {
    preferences?: Record<string, unknown>
  }
}

export interface GetSingleUrlParams {
  resourceId: string | number
  dateFrom: string
  dateTo: string
  compareDateFrom?: string
  compareDateTo?: string
  filters?: Filter[]
  timeframe?: 'daily' | 'weekly' | 'monthly'
}

export const getSingleUrlQueryOptions = ({
  resourceId,
  dateFrom,
  dateTo,
  compareDateFrom,
  compareDateTo,
  filters,
  timeframe = 'daily',
}: GetSingleUrlParams) => {
  const apiFilters = transformFiltersToApi(filters || [])
  const hasCompare = !!(compareDateFrom && compareDateTo)
  const dateGroupBy = timeframe === 'weekly' ? 'week' : timeframe === 'monthly' ? 'month' : 'date'

  // Filter by resource_pk (resources table PK) — no post_type filter needed for non-content pages
  const filtersWithResourceId: Record<string, unknown> = {
    ...apiFilters,
    resource_pk: String(resourceId),
  }

  return queryOptions({
    queryKey: ['single-url', resourceId, dateFrom, dateTo, compareDateFrom, compareDateTo, apiFilters, hasCompare, timeframe, dateGroupBy, filtersWithResourceId],
    queryFn: () =>
      clientRequest.post<SingleUrlResponse>(
        '',
        {
          date_from: dateFrom,
          date_to: dateTo,
          compare: hasCompare,
          ...(hasCompare && {
            previous_date_from: compareDateFrom,
            previous_date_to: compareDateTo,
          }),
          filters: filtersWithResourceId,
          queries: [
            // URL Metrics: Flat format for aggregate totals (no comments)
            {
              id: 'url_metrics',
              sources: [
                'visitors',
                'views',
                'bounce_rate',
                'avg_time_on_page',
                'entry_page',
                'exit_page',
                'exit_rate',
              ],
              group_by: [],
              format: 'flat',
              show_totals: true,
              compare: true,
            },
            // URL Info: Table format to get page metadata
            {
              id: 'url_info',
              sources: ['views'],
              group_by: ['page'],
              columns: [
                'page_uri',
                'page_title',
                'page_wp_id',
                'page_type',
                'resource_id',
                'permalink',
              ],
              format: 'table',
              per_page: 1,
              show_totals: false,
              compare: false,
            },
            // Traffic Trends: Chart format for line chart
            {
              id: 'traffic_trends',
              sources: ['visitors', 'views'],
              group_by: [dateGroupBy],
              format: 'chart',
              show_totals: false,
              compare: true,
            },
            // Top Referrers
            {
              id: 'top_referrers',
              sources: ['visitors'],
              group_by: ['referrer'],
              columns: ['referrer_domain', 'referrer_name', 'referrer_channel', 'visitors'],
              filters: [{ key: 'referrer_domain', operator: 'is_not_empty', value: '' }],
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
                { key: 'referrer_domain', operator: 'is_not_empty', value: '' },
                { key: 'referrer_channel', operator: 'contains', value: 'search' },
              ],
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
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
            // Top Browsers
            {
              id: 'top_browsers',
              sources: ['visitors'],
              group_by: ['browser'],
              columns: ['browser_name', 'visitors'],
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

// Re-export shared types for consumers
export type { TrafficSummaryPeriodData as UrlTrafficSummaryPeriodData }
export { extractTrafficSummaryData as extractUrlTrafficSummaryData }

// ============================================================================
// Traffic Summary Query Options
// ============================================================================

/**
 * Parameters for traffic summary query
 */
export interface GetUrlTrafficSummaryParams {
  resourceId: string | number
  period: FixedDatePeriod
  filters?: Filter[]
}

/**
 * Query options for a single traffic summary period
 */
export const getUrlTrafficSummaryPeriodQueryOptions = ({
  resourceId,
  period,
  filters,
}: GetUrlTrafficSummaryParams) => {
  const apiFilters = transformFiltersToApi(filters || [])

  const filtersWithResourceId: Record<string, unknown> = {
    ...apiFilters,
    resource_pk: String(resourceId),
  }

  const hasCompare = !!(period.compareDateFrom && period.compareDateTo)

  return queryOptions({
    queryKey: ['url-traffic-summary', resourceId, period.id, apiFilters, period.dateFrom, period.dateTo, period.compareDateFrom, period.compareDateTo, hasCompare, filtersWithResourceId],
    queryFn: () =>
      clientRequest.post<{ success: boolean; items: { traffic_summary?: TrafficSummaryPeriodResponse } }>(
        '',
        {
          date_from: period.dateFrom,
          date_to: period.dateTo,
          compare: hasCompare,
          ...(hasCompare && {
            previous_date_from: period.compareDateFrom,
            previous_date_to: period.compareDateTo,
          }),
          filters: filtersWithResourceId,
          queries: [
            {
              id: 'traffic_summary',
              sources: ['visitors', 'views'],
              group_by: [],
              format: 'flat',
              show_totals: true,
              compare: hasCompare,
            },
          ],
        },
        {
          params: {
            action: WordPress.getInstance().getAnalyticsAction(),
          },
        }
      ),
    staleTime: 5 * 60 * 1000,
  })
}

