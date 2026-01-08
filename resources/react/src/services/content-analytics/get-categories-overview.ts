import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { type ApiFilters, transformFiltersToApi } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

// Re-export ApiFilters type for backward compatibility
export type { ApiFilters }

// Metric value with current/previous structure
interface MetricValue {
  current: number | string
  previous: number | string
}

// Flat format response (for metrics query without group_by)
export interface CategoriesMetricsResponse {
  success: boolean
  items: Array<Record<string, string | number>>
  totals: {
    visitors: MetricValue
    views: MetricValue
    published_content: MetricValue
    active_terms: MetricValue
    bounce_rate: MetricValue
    avg_time_on_page: MetricValue
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

// Chart format response (for performance trends)
export interface CategoriesPerformanceChartResponse {
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

// Top term row
export interface TopTermRow {
  term_id: number
  term_name: string
  term_slug: string
  taxonomy_type: string
  term_link?: string
  views: number | string
  visitors: number | string
  published_content: number | string
  previous?: {
    views: number | string
    visitors: number | string
    published_content: number | string
  }
}

// Top content row
export interface TopContentRow {
  resource_id: number
  page_title: string
  page_uri: string
  page_type: string
  page_wp_id: number
  page_uri_id: number
  views: number | string
  visitors: number | string
  comments: number | string
  published_date: string | null
  thumbnail_url: string | null
  previous?: {
    views: number | string
    visitors: number | string
  }
}

// Top author row
export interface TopAuthorRow {
  author_id: number
  author_name: string
  author_avatar?: string
  views: number | string
  visitors: number | string
  published_content: number | string
  previous?: {
    views: number | string
    visitors: number | string
    published_content: number | string
  }
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

// Top country row
export interface TopCountryRow {
  country_code: string
  country_name: string
  visitors: number | string
  previous?: {
    visitors: number | string
  }
}

// Top browser row
export interface TopBrowserRow {
  browser_name: string
  visitors: number | string
  previous?: {
    visitors: number | string
  }
}

// Top OS row
export interface TopOSRow {
  os_name: string
  visitors: number | string
  previous?: {
    visitors: number | string
  }
}

// Top device row
export interface TopDeviceRow {
  device_type_name: string
  visitors: number | string
  previous?: {
    visitors: number | string
  }
}

// Totals value structure
interface TotalsValue {
  current?: number | string
  previous?: number | string
}

// Table format response wrapper
export interface TableQueryResult<T> {
  success: boolean
  data: {
    rows: T[]
    totals?: {
      visitors?: number | string | TotalsValue
      views?: number | string | TotalsValue
      [key: string]: unknown
    }
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

// Batch response structure for categories overview
export interface CategoriesOverviewResponse {
  success: boolean
  items: {
    // Flat format - totals at top level
    categories_metrics?: CategoriesMetricsResponse
    // Chart format - labels and datasets at top level
    categories_performance?: CategoriesPerformanceChartResponse
    // Table format queries for top terms
    top_terms_views?: TableQueryResult<TopTermRow>
    top_terms_publishing?: TableQueryResult<TopTermRow>
    // Top content
    top_content_popular?: TableQueryResult<TopContentRow>
    top_content_commented?: TableQueryResult<TopContentRow>
    top_content_recent?: TableQueryResult<TopContentRow>
    // Top authors (2 tabs for categories context)
    top_authors_views?: TableQueryResult<TopAuthorRow>
    top_authors_publishing?: TableQueryResult<TopAuthorRow>
    // Traffic sources
    top_referrers?: TableQueryResult<TopReferrerRow>
    top_search_engines?: TableQueryResult<TopReferrerRow>
    top_countries?: TableQueryResult<TopCountryRow>
    // Device analytics
    top_browsers?: TableQueryResult<TopBrowserRow>
    top_os?: TableQueryResult<TopOSRow>
    top_devices?: TableQueryResult<TopDeviceRow>
  }
  errors?: Record<string, { code: string; message: string }>
  skipped?: string[]
  meta?: {
    preferences?: Record<string, unknown>
  }
}

export interface GetCategoriesOverviewParams {
  dateFrom: string
  dateTo: string
  compareDateFrom?: string
  compareDateTo?: string
  timeframe?: 'daily' | 'weekly' | 'monthly'
  taxonomy?: string
  filters?: Filter[]
}

export const getCategoriesOverviewQueryOptions = ({
  dateFrom,
  dateTo,
  compareDateFrom,
  compareDateTo,
  timeframe = 'daily',
  taxonomy = 'category',
  filters = [],
}: GetCategoriesOverviewParams) => {
  // Determine the appropriate date group_by based on timeframe
  const dateGroupBy = timeframe === 'monthly' ? 'month' : timeframe === 'weekly' ? 'week' : 'date'
  // Transform UI filters to API format
  const apiFilters = transformFiltersToApi(filters)
  // Check if compare dates are provided
  const hasCompare = !!(compareDateFrom && compareDateTo)

  // Add taxonomy_type filter to all queries
  const taxonomyFilter = { key: 'taxonomy_type', operator: 'is', value: taxonomy }

  return queryOptions({
    queryKey: [
      'categories-overview',
      dateFrom,
      dateTo,
      compareDateFrom,
      compareDateTo,
      timeframe,
      taxonomy,
      apiFilters,
      hasCompare,
      dateGroupBy,
    ],
    queryFn: () =>
      clientRequest.post<CategoriesOverviewResponse>(
        '',
        {
          date_from: dateFrom,
          date_to: dateTo,
          compare: hasCompare,
          ...(hasCompare && {
            previous_date_from: compareDateFrom,
            previous_date_to: compareDateTo,
          }),
          filters: apiFilters,
          queries: [
            // Categories Metrics: Flat format for aggregate totals
            {
              id: 'categories_metrics',
              sources: ['visitors', 'views', 'published_content', 'active_terms', 'bounce_rate', 'avg_time_on_page'],
              group_by: [],
              format: 'flat',
              show_totals: true,
              compare: true,
              filters: [taxonomyFilter],
            },
            // Categories Performance: Chart format for line chart
            {
              id: 'categories_performance',
              sources: ['visitors', 'views', 'published_content'],
              group_by: [dateGroupBy],
              format: 'chart',
              show_totals: false,
              compare: true,
              filters: [taxonomyFilter],
            },
            // Top Terms - By Views
            {
              id: 'top_terms_views',
              sources: ['views', 'visitors', 'published_content'],
              group_by: ['taxonomy'],
              per_page: 5,
              order_by: 'views',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
              filters: [taxonomyFilter],
            },
            // Top Terms - By Publishing (content count)
            {
              id: 'top_terms_publishing',
              sources: ['views', 'visitors', 'published_content'],
              group_by: ['taxonomy'],
              per_page: 5,
              order_by: 'published_content',
              order: 'DESC',
              format: 'table',
              show_totals: false,
              compare: false,
              filters: [taxonomyFilter],
            },
            // Top Content - Most Popular (by views)
            {
              id: 'top_content_popular',
              sources: ['views', 'visitors'],
              group_by: ['page'],
              columns: [
                'resource_id',
                'page_title',
                'page_uri',
                'page_type',
                'page_wp_id',
                'views',
                'visitors',
                'published_date',
                'thumbnail_url',
                'comments',
              ],
              per_page: 5,
              order_by: 'views',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
              filters: [taxonomyFilter],
            },
            // Top Content - Most Commented (by comments)
            {
              id: 'top_content_commented',
              sources: ['views', 'visitors', 'comments'],
              group_by: ['page'],
              columns: [
                'resource_id',
                'page_title',
                'page_uri',
                'page_type',
                'page_wp_id',
                'views',
                'visitors',
                'published_date',
                'thumbnail_url',
                'comments',
              ],
              per_page: 5,
              order_by: 'comments',
              order: 'DESC',
              format: 'table',
              show_totals: false,
              compare: false,
              filters: [taxonomyFilter],
            },
            // Top Content - Most Recent (by published date)
            {
              id: 'top_content_recent',
              sources: ['views', 'visitors'],
              group_by: ['page'],
              columns: [
                'resource_id',
                'page_title',
                'page_uri',
                'page_type',
                'page_wp_id',
                'views',
                'visitors',
                'published_date',
                'thumbnail_url',
                'comments',
              ],
              per_page: 5,
              order_by: 'published_date',
              order: 'DESC',
              format: 'table',
              show_totals: false,
              compare: false,
              filters: [taxonomyFilter],
            },
            // Top Authors - By Views (only 2 tabs for categories context)
            {
              id: 'top_authors_views',
              sources: ['views', 'visitors', 'published_content'],
              group_by: ['author'],
              per_page: 5,
              order_by: 'views',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
              filters: [taxonomyFilter],
            },
            // Top Authors - By Publishing
            {
              id: 'top_authors_publishing',
              sources: ['views', 'visitors', 'published_content'],
              group_by: ['author'],
              per_page: 5,
              order_by: 'published_content',
              order: 'DESC',
              format: 'table',
              show_totals: false,
              compare: false,
              filters: [taxonomyFilter],
            },
            // Top Referrers
            {
              id: 'top_referrers',
              sources: ['visitors'],
              group_by: ['referrer'],
              columns: ['referrer_domain', 'referrer_name', 'referrer_channel', 'visitors'],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
              filters: [taxonomyFilter],
            },
            // Top Search Engines
            {
              id: 'top_search_engines',
              sources: ['visitors'],
              group_by: ['referrer'],
              columns: ['referrer_domain', 'referrer_name', 'referrer_channel', 'visitors'],
              filters: [taxonomyFilter, { key: 'referrer_channel', operator: 'is', value: 'search' }],
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
              filters: [taxonomyFilter],
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
              filters: [taxonomyFilter],
            },
            // Top Operating Systems
            {
              id: 'top_os',
              sources: ['visitors'],
              group_by: ['os'],
              columns: ['os_name', 'visitors'],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
              filters: [taxonomyFilter],
            },
            // Top Device Categories
            {
              id: 'top_devices',
              sources: ['visitors'],
              group_by: ['device_type'],
              columns: ['device_type_name', 'visitors'],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
              filters: [taxonomyFilter],
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
