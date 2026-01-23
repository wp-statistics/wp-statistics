import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { transformFiltersToApi } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'
import type {
  TrafficTrendsChartResponse,
  TopCountryRow,
  BrowserRow,
  OperatingSystemRow,
  DeviceTypeRow,
  TableQueryResult,
  TopReferrersResponse,
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

// Post info row from table query
export interface PostInfoRow {
  page_uri: string
  page_title: string
  page_wp_id: number | null
  page_type?: string
  published_date?: string | null
  comments?: number
  author_name?: string | null
  thumbnail_url?: string | null
  permalink?: string | null
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
                'published_date',
                'comments',
                'author_name',
                'thumbnail_url',
                'permalink',
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
