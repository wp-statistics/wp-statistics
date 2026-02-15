import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { transformFiltersToApi } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

// Metric value with current/previous structure
interface MetricValue {
  current: number | string
  previous?: number | string
}

// Totals structure for flat format
interface CountryMetricsTotals {
  visitors?: MetricValue
  views?: MetricValue
  bounce_rate?: MetricValue
  avg_session_duration?: MetricValue
}

// Single country metrics response (flat format)
export interface SingleCountryMetricsResponse {
  success: boolean
  items: Array<Record<string, string | number>>
  totals: CountryMetricsTotals
  meta: {
    date_from: string
    date_to: string
    cached: boolean
    cache_ttl: number
    compare_from?: string
    compare_to?: string
  }
}

// Country info row from table query (using group_by: ['country'])
export interface CountryInfoRow {
  country_code: string
  country_name: string
  visitors?: number | string
  views?: number | string
}

// Country info response (table format)
export interface CountryInfoResponse {
  success: boolean
  data: {
    rows: CountryInfoRow[]
  }
}

// Region row for top regions
export interface RegionRow {
  region_code: string
  region_name: string
  visitors: number | string
  views: number | string
  previous?: {
    visitors?: number
    views?: number
  }
}

// City row for top cities
export interface CityRow {
  city_id: number
  city_name: string
  city_region_name: string
  visitors: number | string
  views: number | string
  previous?: {
    visitors?: number
    views?: number
  }
}

// Referrer row for top referrer
export interface ReferrerRow {
  referrer_domain: string
  referrer_name?: string
  visitors: number | string
  previous?: { visitors?: number }
}

// Entry page row for top entry pages
export interface EntryPageRow {
  page_uri: string
  page_title: string
  sessions: number | string
  previous?: { sessions?: number }
}

// Search engine row for top search engines
export interface SearchEngineRow {
  referrer_name: string
  referrer_domain: string
  visitors: number | string
  previous?: { visitors?: number }
}

// Browser row for top browsers
export interface BrowserRow {
  browser_name: string
  visitors: number | string
  previous?: { visitors?: number }
}

// Chart response for traffic trends
export interface TrafficTrendsChartResponse {
  success: boolean
  data: {
    labels: string[]
    datasets: {
      [key: string]: {
        current: (number | null)[]
        previous?: (number | null)[]
      }
    }
  }
  meta?: {
    date_from: string
    date_to: string
    compare?: boolean
    compare_from?: string
    compare_to?: string
  }
}

// Table query result structure
export interface TableQueryResult<T> {
  success: boolean
  data: {
    rows: T[]
    totals?: Record<string, number | string>
  }
  meta?: {
    date_from: string
    date_to: string
    page: number
    per_page: number
    total_rows: number
  }
}

// Batch response structure for single country
export interface SingleCountryResponse {
  success: boolean
  items: {
    country_info?: CountryInfoResponse
    country_metrics?: SingleCountryMetricsResponse
    traffic_trends?: TrafficTrendsChartResponse
    top_regions?: TableQueryResult<RegionRow>
    top_cities?: TableQueryResult<CityRow>
    top_referrer?: TableQueryResult<ReferrerRow>
    top_entry_pages?: TableQueryResult<EntryPageRow>
    top_referrers?: TableQueryResult<ReferrerRow>
    top_search_engines?: TableQueryResult<SearchEngineRow>
    top_browsers?: TableQueryResult<BrowserRow>
  }
  errors?: Record<string, { code: string; message: string }>
  skipped?: string[]
  meta?: {
    preferences?: Record<string, unknown>
  }
}

export interface GetSingleCountryParams {
  countryCode: string
  dateFrom: string
  dateTo: string
  compareDateFrom?: string
  compareDateTo?: string
  filters?: Filter[]
  timeframe?: 'daily' | 'weekly' | 'monthly'
}

export const getSingleCountryQueryOptions = ({
  countryCode,
  dateFrom,
  dateTo,
  compareDateFrom,
  compareDateTo,
  filters,
  timeframe = 'daily',
}: GetSingleCountryParams) => {
  // Transform UI filters to API format (ensure filters is an array)
  const apiFilters = transformFiltersToApi(filters || [])
  // Check if compare dates are provided
  const hasCompare = !!(compareDateFrom && compareDateTo)

  // Determine group_by based on timeframe
  const chartGroupBy = timeframe === 'daily' ? 'date' : timeframe === 'weekly' ? 'week' : 'month'

  return queryOptions({
    queryKey: [
      'single-country',
      countryCode,
      dateFrom,
      dateTo,
      compareDateFrom,
      compareDateTo,
      apiFilters,
      hasCompare,
      timeframe,
      chartGroupBy,
    ],
    queryFn: () => {
      // Country filter for queries - created inside queryFn to avoid dependency issues
      const countryFilter = [{ key: 'country', operator: 'is', value: countryCode }]

      return clientRequest.post<SingleCountryResponse>(
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
            // Country Info: Get country name from country code
            {
              id: 'country_info',
              sources: ['visitors', 'views'],
              group_by: ['country'],
              columns: ['country_code', 'country_name', 'visitors', 'views'],
              format: 'table',
              per_page: 1,
              show_totals: false,
              compare: false,
              filters: countryFilter,
            },
            // Country Metrics: Flat format for aggregate totals
            {
              id: 'country_metrics',
              sources: ['visitors', 'views', 'bounce_rate', 'avg_session_duration'],
              group_by: [],
              format: 'flat',
              show_totals: true,
              compare: true,
              filters: countryFilter,
            },
            // Traffic Trends: Chart format for performance over time
            {
              id: 'traffic_trends',
              sources: ['visitors', 'views'],
              group_by: [chartGroupBy],
              format: 'chart',
              show_totals: false,
              compare: true,
              filters: countryFilter,
            },
            // Top Regions: 5 items ordered by visitors
            {
              id: 'top_regions',
              sources: ['visitors', 'views'],
              group_by: ['region'],
              columns: ['region_code', 'region_name', 'visitors', 'views'],
              format: 'table',
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              show_totals: false,
              compare: true,
              filters: countryFilter,
            },
            // Top Cities: 5 items ordered by visitors
            {
              id: 'top_cities',
              sources: ['visitors', 'views'],
              group_by: ['city'],
              columns: ['city_id', 'city_name', 'city_region_name', 'visitors', 'views'],
              format: 'table',
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              show_totals: false,
              compare: true,
              filters: countryFilter,
            },
            // Top Referrer: 1 item for metric card
            {
              id: 'top_referrer',
              sources: ['visitors'],
              group_by: ['referrer'],
              columns: ['referrer_domain', 'visitors'],
              format: 'table',
              per_page: 1,
              order_by: 'visitors',
              order: 'DESC',
              show_totals: false,
              compare: false,
              filters: countryFilter,
            },
            // Top Entry Pages: 5 items ordered by sessions
            {
              id: 'top_entry_pages',
              sources: ['sessions'],
              group_by: ['entry_page'],
              columns: ['page_uri', 'page_title', 'sessions'],
              format: 'table',
              per_page: 5,
              order_by: 'sessions',
              order: 'DESC',
              show_totals: false,
              compare: true,
              filters: countryFilter,
            },
            // Top Referrers: 5 items ordered by visitors
            {
              id: 'top_referrers',
              sources: ['visitors'],
              group_by: ['referrer'],
              columns: ['referrer_domain', 'referrer_name', 'visitors'],
              format: 'table',
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              show_totals: false,
              compare: true,
              filters: countryFilter,
            },
            // Top Search Engines: 5 items ordered by visitors
            {
              id: 'top_search_engines',
              sources: ['visitors'],
              group_by: ['search_engine'],
              columns: ['referrer_name', 'referrer_domain', 'visitors'],
              format: 'table',
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              show_totals: false,
              compare: true,
              filters: countryFilter,
            },
            // Top Browsers: 5 items ordered by visitors
            {
              id: 'top_browsers',
              sources: ['visitors'],
              group_by: ['browser'],
              columns: ['browser_name', 'visitors'],
              format: 'table',
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              show_totals: false,
              compare: true,
              filters: countryFilter,
            },
          ],
        },
        {
          params: {
            action: WordPress.getInstance().getAnalyticsAction(),
          },
        }
      )
    },
  })
}
