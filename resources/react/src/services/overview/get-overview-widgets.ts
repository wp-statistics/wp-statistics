/**
 * Per-widget query options for the Overview Dashboard.
 *
 * Each widget fetches its own data independently using its own date range.
 * Supports comparison when enabled globally.
 */

import { queryOptions } from '@tanstack/react-query'

import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

export interface WidgetQueryParams {
  dateFrom: string
  dateTo: string
  compareDateFrom?: string
  compareDateTo?: string
}

function compareParams(params: WidgetQueryParams) {
  const hasCompare = !!(params.compareDateFrom && params.compareDateTo)
  return {
    compare: hasCompare,
    ...(hasCompare && { previous_date_from: params.compareDateFrom, previous_date_to: params.compareDateTo }),
  }
}

// eslint-disable-next-line @typescript-eslint/no-explicit-any
function postAnalytics<T = any>(body: Record<string, unknown>) {
  return clientRequest.post<T>('', body, {
    params: { action: WordPress.getInstance().getAnalyticsAction() },
  })
}

function tableQueryOptions(
  params: WidgetQueryParams,
  cacheKey: string,
  query: Record<string, unknown>,
) {
  const cp = compareParams(params)
  return queryOptions({
    queryKey: ['overview-widget', cacheKey, params.dateFrom, params.dateTo, cp.compare, params.compareDateFrom],
    queryFn: () =>
      postAnalytics({
        date_from: params.dateFrom,
        date_to: params.dateTo,
        ...cp,
        queries: [{ ...query, compare: cp.compare }],
      }),
  })
}

export const getOverviewMetricsQueryOptions = (params: WidgetQueryParams) => {
  const cp = compareParams(params)
  return queryOptions({
    queryKey: ['overview-widget', 'metrics', params.dateFrom, params.dateTo, cp.compare, params.compareDateFrom],
    queryFn: () =>
      postAnalytics({
        date_from: params.dateFrom,
        date_to: params.dateTo,
        ...cp,
        queries: [
          {
            id: 'metrics',
            sources: ['visitors', 'views', 'sessions', 'avg_session_duration', 'bounce_rate', 'pages_per_session'],
            group_by: [],
            format: 'flat',
            show_totals: true,
            compare: cp.compare,
          },
        ],
      }),
  })
}

export const getOverviewTrafficTrendsQueryOptions = (
  params: WidgetQueryParams & { timeframe?: 'daily' | 'weekly' | 'monthly' }
) => {
  const { dateFrom, dateTo, timeframe = 'daily' } = params
  const cp = compareParams(params)
  const dateGroupBy = timeframe === 'monthly' ? 'month' : timeframe === 'weekly' ? 'week' : 'date'
  return queryOptions({
    queryKey: ['overview-widget', 'traffic-trends', dateFrom, dateTo, timeframe, dateGroupBy, cp.compare, params.compareDateFrom],
    queryFn: () =>
      postAnalytics({
        date_from: dateFrom,
        date_to: dateTo,
        ...cp,
        queries: [
          {
            id: 'traffic_trends',
            sources: ['visitors', 'views'],
            group_by: [dateGroupBy],
            format: 'chart',
            show_totals: cp.compare,
            compare: cp.compare,
          },
        ],
      }),
  })
}

export const getOverviewTopPagesQueryOptions = (params: WidgetQueryParams) =>
  tableQueryOptions(params, 'top-pages', {
    id: 'top_pages',
    sources: ['views', 'visitors'],
    group_by: ['page'],
    columns: ['page_uri', 'page_title', 'page_type', 'page_wp_id', 'visitors', 'views'],
    per_page: 5,
    order_by: 'views',
    order: 'DESC',
    format: 'table',
    show_totals: true,
  })

export const getOverviewTopReferrersQueryOptions = (params: WidgetQueryParams) =>
  tableQueryOptions(params, 'top-referrers', {
    id: 'top_referrers',
    sources: ['visitors'],
    group_by: ['referrer'],
    columns: ['referrer_domain', 'referrer_name', 'referrer_channel', 'visitors'],
    per_page: 5,
    order_by: 'visitors',
    order: 'DESC',
    format: 'table',
    show_totals: true,
  })

export const getOverviewTopCountriesQueryOptions = (params: WidgetQueryParams) =>
  tableQueryOptions(params, 'top-countries', {
    id: 'top_countries',
    sources: ['visitors'],
    group_by: ['country'],
    columns: ['country_code', 'country_name', 'visitors'],
    per_page: 5,
    order_by: 'visitors',
    order: 'DESC',
    format: 'table',
    show_totals: true,
  })

export const getOverviewTopBrowsersQueryOptions = (params: WidgetQueryParams) =>
  tableQueryOptions(params, 'top-browsers', {
    id: 'top_browsers',
    sources: ['visitors'],
    group_by: ['browser'],
    columns: ['browser_name', 'browser_id', 'visitors'],
    per_page: 5,
    order_by: 'visitors',
    order: 'DESC',
    format: 'table',
    show_totals: true,
  })

export const getOverviewTopSearchEnginesQueryOptions = (params: WidgetQueryParams) =>
  tableQueryOptions(params, 'top-search-engines', {
    id: 'top_search_engines',
    sources: ['visitors'],
    group_by: ['search_engine'],
    columns: ['search_engine_name', 'visitors'],
    per_page: 5,
    order_by: 'visitors',
    order: 'DESC',
    format: 'table',
    show_totals: true,
  })

export const getOverviewTopSocialMediaQueryOptions = (params: WidgetQueryParams) =>
  tableQueryOptions(params, 'top-social-media', {
    id: 'top_social_media',
    sources: ['visitors'],
    group_by: ['social_media'],
    columns: ['social_media_name', 'visitors'],
    per_page: 5,
    order_by: 'visitors',
    order: 'DESC',
    format: 'table',
    show_totals: true,
  })

export const getOverviewTopCitiesQueryOptions = (params: WidgetQueryParams) =>
  tableQueryOptions(params, 'top-cities', {
    id: 'top_cities',
    sources: ['visitors'],
    group_by: ['city'],
    columns: ['city_name', 'country_code', 'country_name', 'visitors'],
    per_page: 5,
    order_by: 'visitors',
    order: 'DESC',
    format: 'table',
    show_totals: true,
  })

export const getOverviewTopOSQueryOptions = (params: WidgetQueryParams) =>
  tableQueryOptions(params, 'top-os', {
    id: 'top_os',
    sources: ['visitors'],
    group_by: ['os'],
    columns: ['os_name', 'os_id', 'visitors'],
    per_page: 5,
    order_by: 'visitors',
    order: 'DESC',
    format: 'table',
    show_totals: true,
  })

export const getOverviewTopDeviceCategoriesQueryOptions = (params: WidgetQueryParams) =>
  tableQueryOptions(params, 'top-device-categories', {
    id: 'top_device_categories',
    sources: ['visitors'],
    group_by: ['device_type'],
    columns: ['device_type_name', 'device_type_id', 'visitors'],
    per_page: 5,
    order_by: 'visitors',
    order: 'DESC',
    format: 'table',
    show_totals: true,
  })

export const getOverviewTopVisitorsQueryOptions = (params: WidgetQueryParams) =>
  tableQueryOptions(params, 'top-visitors', {
    id: 'top_visitors',
    sources: ['visitors'],
    group_by: ['visitor'],
    columns: [
      'visitor_id', 'visitor_hash', 'ip_address', 'user_id', 'user_login',
      'total_views', 'country_code', 'country_name', 'os_name', 'browser_name',
      'device_type_name', 'referrer_domain',
    ],
    per_page: 5,
    order_by: 'total_views',
    order: 'DESC',
    format: 'table',
    show_totals: false,
  })
