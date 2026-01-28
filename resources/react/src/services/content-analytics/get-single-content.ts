import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { transformFiltersToApi } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import type { FixedDatePeriod, FixedDatePeriodId } from '@/lib/fixed-date-ranges'
import { WordPress } from '@/lib/wordpress'

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

// Totals structure for flat format
interface MetricsTotals {
  visitors?: MetricValue
  views?: MetricValue
  bounce_rate?: MetricValue
  avg_time_on_page?: MetricValue
  entry_page?: MetricValue
  exit_page?: MetricValue
  exit_rate?: MetricValue
  comments?: MetricValue
}

// Single content metrics response (flat format)
export interface SingleContentMetricsResponse {
  success: boolean
  items: Array<Record<string, string | number>>
  totals: MetricsTotals
  meta: {
    date_from: string
    date_to: string
    cached: boolean
    cache_ttl: number
    compare_from?: string
    compare_to?: string
  }
}

// Term/taxonomy data from API
export interface TermInfo {
  term_id: number
  name: string
  slug: string
  taxonomy: string
}

// Post info row from table query
export interface PostInfoRow {
  page_uri: string
  page_title: string
  page_wp_id: number | null
  page_type?: string
  post_type_label?: string | null
  published_date?: string | null
  modified_date?: string | null
  comments?: number
  author_id?: number | null
  author_name?: string | null
  thumbnail_url?: string | null
  permalink?: string | null
  cached_terms?: TermInfo[]
}

// Post info response (table format)
export interface PostInfoResponse {
  success: boolean
  data: {
    rows: PostInfoRow[]
  }
}

// Batch response structure for single content
export interface SingleContentResponse {
  success: boolean
  items: {
    content_metrics?: SingleContentMetricsResponse
    post_info?: PostInfoResponse
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

export interface GetSingleContentParams {
  postId: string | number
  dateFrom: string
  dateTo: string
  compareDateFrom?: string
  compareDateTo?: string
  filters?: Filter[]
  timeframe?: 'daily' | 'weekly' | 'monthly'
}

export const getSingleContentQueryOptions = ({
  postId,
  dateFrom,
  dateTo,
  compareDateFrom,
  compareDateTo,
  filters,
  timeframe = 'daily',
}: GetSingleContentParams) => {
  // Transform UI filters to API format (ensure filters is an array)
  const apiFilters = transformFiltersToApi(filters || [])
  // Check if compare dates are provided
  const hasCompare = !!(compareDateFrom && compareDateTo)

  // Map timeframe to API group_by value
  const dateGroupBy = timeframe === 'weekly' ? 'week' : timeframe === 'monthly' ? 'month' : 'date'

  // Get all queryable post types to filter content resources
  // This ensures we only match content (post, page, etc.) and not authors/categories
  const queryablePostTypes = WordPress.getInstance().getQueryablePostTypes()

  // Add resource_id and post_type filters for the specific post
  // post_type uses 'in' operator to match any content type
  const filtersWithResourceId: Record<string, unknown> = {
    ...apiFilters,
    resource_id: String(postId),
    post_type: { in: queryablePostTypes },
  }

  return queryOptions({
    queryKey: ['single-content', postId, dateFrom, dateTo, compareDateFrom, compareDateTo, apiFilters, hasCompare, timeframe],
    queryFn: () =>
      clientRequest.post<SingleContentResponse>(
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
            // Content Metrics: Flat format for aggregate totals
            {
              id: 'content_metrics',
              sources: [
                'visitors',
                'views',
                'bounce_rate',
                'avg_time_on_page',
                'entry_page',
                'exit_page',
                'exit_rate',
                'comments',
              ],
              group_by: [],
              format: 'flat',
              show_totals: true,
              compare: true,
            },
            // Post Info: Table format to get page metadata
            {
              id: 'post_info',
              sources: ['views'],
              group_by: ['page'],
              columns: [
                'page_uri',
                'page_title',
                'page_wp_id',
                'page_type',
                'post_type_label',
                'published_date',
                'modified_date',
                'comments',
                'author_id',
                'author_name',
                'thumbnail_url',
                'permalink',
                'cached_terms',
              ],
              format: 'table',
              per_page: 1,
              show_totals: false,
              compare: false,
            },
            // Traffic Trends: Chart format for line chart (visitors + views only, no published_content for single content)
            {
              id: 'traffic_trends',
              sources: ['visitors', 'views'],
              group_by: [dateGroupBy],
              format: 'chart',
              show_totals: false,
              compare: true,
            },
            // Top Referrers: Table format for top referrers widget
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
            // Top Search Engines: Table format for top search engines widget
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

// ============================================================================
// Traffic Summary Types and Query Options
// ============================================================================

/**
 * Traffic summary data for a single period
 */
export interface TrafficSummaryPeriodData {
  visitors: number
  views: number
  previous?: {
    visitors: number
    views: number
  }
}

/**
 * Response structure for a single period query
 */
export interface TrafficSummaryPeriodResponse {
  success: boolean
  totals: {
    visitors?: MetricValue
    views?: MetricValue
  }
}

/**
 * Parameters for traffic summary query
 */
export interface GetTrafficSummaryParams {
  postId: string | number
  period: FixedDatePeriod
  filters?: Filter[]
}

/**
 * Query options for a single traffic summary period
 *
 * This is designed to be used with useQueries for parallel fetching
 * of all 5 fixed time periods.
 */
export const getTrafficSummaryPeriodQueryOptions = ({
  postId,
  period,
  filters,
}: GetTrafficSummaryParams) => {
  // Transform UI filters to API format
  const apiFilters = transformFiltersToApi(filters || [])

  // Get all queryable post types
  const queryablePostTypes = WordPress.getInstance().getQueryablePostTypes()

  // Add resource_id and post_type filters
  const filtersWithResourceId: Record<string, unknown> = {
    ...apiFilters,
    resource_id: String(postId),
    post_type: { in: queryablePostTypes },
  }

  // Check if this period has comparison dates
  const hasCompare = !!(period.compareDateFrom && period.compareDateTo)

  return queryOptions({
    queryKey: ['traffic-summary', postId, period.id, apiFilters],
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
    // Cache for 5 minutes since fixed date ranges don't change often
    staleTime: 5 * 60 * 1000,
  })
}

/**
 * Helper to extract traffic summary data from query result
 */
export function extractTrafficSummaryData(
  response: { data?: { items?: { traffic_summary?: TrafficSummaryPeriodResponse } } } | undefined,
  periodId: FixedDatePeriodId
): TrafficSummaryPeriodData | null {
  const summaryResponse = response?.data?.items?.traffic_summary
  if (!summaryResponse?.success) return null

  const totals = summaryResponse.totals
  if (!totals) return null

  const visitors = typeof totals.visitors?.current === 'number'
    ? totals.visitors.current
    : Number(totals.visitors?.current) || 0

  const views = typeof totals.views?.current === 'number'
    ? totals.views.current
    : Number(totals.views?.current) || 0

  const result: TrafficSummaryPeriodData = { visitors, views }

  // Add previous values if available (not for 'total' period)
  if (periodId !== 'total' && totals.visitors?.previous !== undefined) {
    result.previous = {
      visitors: typeof totals.visitors.previous === 'number'
        ? totals.visitors.previous
        : Number(totals.visitors.previous) || 0,
      views: typeof totals.views?.previous === 'number'
        ? totals.views.previous
        : Number(totals.views?.previous) || 0,
    }
  }

  return result
}
