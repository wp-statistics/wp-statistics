import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { transformFiltersToApi } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import type { FixedDatePeriod, FixedDatePeriodId } from '@/lib/fixed-date-ranges'
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
import type { ContentRow } from './get-categories-overview'

// Re-export types for consumers
export type { TopCountryRow, BrowserRow, OperatingSystemRow, DeviceTypeRow, ContentRow }

// Metric value with current/previous structure
interface MetricValue {
  current: number | string
  previous?: number | string
}

// Totals structure for flat format
interface MetricsTotals {
  visitors?: MetricValue
  views?: MetricValue
  published_content?: MetricValue
  bounce_rate?: MetricValue
  avg_time_on_page?: MetricValue
}

// Single category metrics response (flat format)
export interface SingleCategoryMetricsResponse {
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

// Category info row from table query (grouped by taxonomy)
export interface CategoryInfoRow {
  term_id: number
  term_name: string
  term_slug: string
  taxonomy_type?: string
  visitors?: number | string
  views?: number | string
  published_content?: number | string
  bounce_rate?: number | string
  avg_time_on_page?: number | string
  previous?: {
    visitors?: number | string
    views?: number | string
    published_content?: number | string
    bounce_rate?: number | string
    avg_time_on_page?: number | string
  }
}

// Category info response (table format)
export interface CategoryInfoResponse {
  success: boolean
  data: {
    rows: CategoryInfoRow[]
  }
}

// Batch response structure for single category
export interface SingleCategoryResponse {
  success: boolean
  items: {
    category_info?: CategoryInfoResponse
    category_metrics?: SingleCategoryMetricsResponse
    traffic_trends?: TrafficTrendsChartResponse
    top_content?: TableQueryResult<ContentRow>
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

export interface GetSingleCategoryParams {
  termId: string | number
  taxonomyType?: string
  dateFrom: string
  dateTo: string
  compareDateFrom?: string
  compareDateTo?: string
  filters?: Filter[]
  timeframe?: 'daily' | 'weekly' | 'monthly'
}

export const getSingleCategoryQueryOptions = ({
  termId,
  taxonomyType = 'category',
  dateFrom,
  dateTo,
  compareDateFrom,
  compareDateTo,
  filters,
  timeframe = 'daily',
}: GetSingleCategoryParams) => {
  // Transform UI filters to API format (ensure filters is an array)
  const apiFilters = transformFiltersToApi(filters || [])
  // Check if compare dates are provided
  const hasCompare = !!(compareDateFrom && compareDateTo)

  // Map timeframe to API group_by value
  const dateGroupBy = timeframe === 'weekly' ? 'week' : timeframe === 'monthly' ? 'month' : 'date'

  // Global filters: taxonomy_type only (for category_info which uses client-side term filtering)
  const filtersWithTaxonomy: Record<string, unknown> = {
    ...apiFilters,
    taxonomy_type: { is: taxonomyType },
  }

  // Query-level taxonomy filter for term-specific data (for queries that support it)
  const taxonomyFilter = { key: 'taxonomy', operator: 'is', value: String(termId) }

  return queryOptions({
    queryKey: ['single-category', termId, taxonomyType, dateFrom, dateTo, compareDateFrom, compareDateTo, apiFilters, hasCompare, timeframe],
    queryFn: () =>
      clientRequest.post<SingleCategoryResponse>(
        '',
        {
          date_from: dateFrom,
          date_to: dateTo,
          compare: hasCompare,
          ...(hasCompare && {
            previous_date_from: compareDateFrom,
            previous_date_to: compareDateTo,
          }),
          filters: filtersWithTaxonomy,
          queries: [
            // Category Info: Get term metadata and basic metrics
            // Uses taxonomy filter to get only the specific term
            // Only uses visitors/views/published_content sources (like top_terms in categories overview)
            {
              id: 'category_info',
              sources: ['visitors', 'views', 'published_content'],
              group_by: ['taxonomy'],
              columns: [
                'term_id',
                'term_name',
                'term_slug',
                'taxonomy_type',
                'visitors',
                'views',
                'published_content',
              ],
              filters: [taxonomyFilter],
              format: 'table',
              per_page: 10,
              order_by: 'views',
              order: 'DESC',
              show_totals: false,
              compare: true,
            },
            // Category Metrics: Get detailed metrics for the specific term
            {
              id: 'category_metrics',
              sources: ['visitors', 'views', 'published_content', 'bounce_rate', 'avg_time_on_page'],
              group_by: [],
              filters: [taxonomyFilter],
              format: 'flat',
              show_totals: true,
              compare: true,
            },
            // Traffic Trends: Chart format for line chart (filtered by specific term)
            {
              id: 'traffic_trends',
              sources: ['visitors', 'views'],
              group_by: [dateGroupBy],
              filters: [taxonomyFilter],
              format: 'chart',
              show_totals: false,
              compare: true,
            },
            // Top Content: Pages with content in this specific term
            {
              id: 'top_content',
              sources: ['visitors', 'views', 'comments'],
              group_by: ['page'],
              columns: ['page_wp_id', 'page_title', 'page_uri', 'page_type', 'visitors', 'views', 'comments', 'published_date'],
              filters: [taxonomyFilter],
              per_page: 15,
              order_by: 'views',
              order: 'DESC',
              format: 'table',
              show_totals: false,
              compare: true,
            },
            // Top Referrers (filtered by specific term)
            {
              id: 'top_referrers',
              sources: ['visitors'],
              group_by: ['referrer'],
              columns: ['referrer_domain', 'referrer_name', 'referrer_channel', 'visitors'],
              filters: [
                taxonomyFilter,
                { key: 'referrer_domain', operator: 'is_not_empty', value: '' },
              ],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
            // Top Search Engines (filtered by specific term)
            {
              id: 'top_search_engines',
              sources: ['visitors'],
              group_by: ['referrer'],
              columns: ['referrer_domain', 'referrer_name', 'visitors'],
              filters: [
                taxonomyFilter,
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
            // Top Countries (filtered by specific term)
            {
              id: 'top_countries',
              sources: ['visitors'],
              group_by: ['country'],
              columns: ['country_code', 'country_name', 'visitors'],
              filters: [taxonomyFilter],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
            // Top Browsers (filtered by specific term)
            {
              id: 'top_browsers',
              sources: ['visitors'],
              group_by: ['browser'],
              columns: ['browser_name', 'visitors'],
              filters: [taxonomyFilter],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
            // Top Operating Systems (filtered by specific term)
            {
              id: 'top_operating_systems',
              sources: ['visitors'],
              group_by: ['os'],
              columns: ['os_name', 'visitors'],
              filters: [taxonomyFilter],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
            // Top Device Categories (filtered by specific term)
            {
              id: 'top_device_categories',
              sources: ['visitors'],
              group_by: ['device_type'],
              columns: ['device_type_name', 'visitors'],
              filters: [taxonomyFilter],
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

/**
 * Extract metrics for a specific term from the category_info response
 */
export function extractTermMetrics(
  categoryInfoResponse: CategoryInfoResponse | undefined,
  termId: string | number
): CategoryInfoRow | null {
  if (!categoryInfoResponse?.data?.rows) return null

  // Convert termId to number for comparison
  // Also handle API returning term_id as string by using Number() for comparison
  const termIdNum = typeof termId === 'string' ? parseInt(termId, 10) : termId
  return categoryInfoResponse.data.rows.find(row => Number(row.term_id) === termIdNum) || null
}

// ============================================================================
// Traffic Summary Types and Query Options
// ============================================================================

/**
 * Traffic summary data for a single period
 */
export interface CategoryTrafficSummaryPeriodData {
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
export interface CategoryTrafficSummaryPeriodResponse {
  success: boolean
  totals: {
    visitors?: MetricValue
    views?: MetricValue
  }
}

/**
 * Parameters for category traffic summary query
 */
export interface GetCategoryTrafficSummaryParams {
  termId: string | number
  taxonomyType?: string
  period: FixedDatePeriod
  filters?: Filter[]
}

/**
 * Query options for a single category traffic summary period
 *
 * This is designed to be used with useQueries for parallel fetching
 * of all 5 fixed time periods.
 */
export const getCategoryTrafficSummaryPeriodQueryOptions = ({
  termId,
  taxonomyType = 'category',
  period,
  filters,
}: GetCategoryTrafficSummaryParams) => {
  // Transform UI filters to API format
  const apiFilters = transformFiltersToApi(filters || [])

  // Global filters: taxonomy_type only
  const filtersWithTaxonomy: Record<string, unknown> = {
    ...apiFilters,
    taxonomy_type: { is: taxonomyType },
  }

  // Query-level taxonomy filter for term-specific data
  const taxonomyFilter = { key: 'taxonomy', operator: 'is', value: String(termId) }

  // Check if this period has comparison dates
  const hasCompare = !!(period.compareDateFrom && period.compareDateTo)

  return queryOptions({
    queryKey: ['category-traffic-summary', termId, taxonomyType, period.id, apiFilters],
    queryFn: () =>
      clientRequest.post<{ success: boolean; items: { traffic_summary?: CategoryTrafficSummaryPeriodResponse } }>(
        '',
        {
          date_from: period.dateFrom,
          date_to: period.dateTo,
          compare: hasCompare,
          ...(hasCompare && {
            previous_date_from: period.compareDateFrom,
            previous_date_to: period.compareDateTo,
          }),
          filters: filtersWithTaxonomy,
          queries: [
            {
              id: 'traffic_summary',
              sources: ['visitors', 'views'],
              group_by: [],
              filters: [taxonomyFilter],
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
 * Helper to extract category traffic summary data from query result
 */
export function extractCategoryTrafficSummaryData(
  response: { data?: { items?: { traffic_summary?: CategoryTrafficSummaryPeriodResponse } } } | undefined,
  periodId: FixedDatePeriodId
): CategoryTrafficSummaryPeriodData | null {
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

  const result: CategoryTrafficSummaryPeriodData = { visitors, views }

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
