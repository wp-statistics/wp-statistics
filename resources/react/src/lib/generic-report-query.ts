/**
 * Generic Report Query Factory
 *
 * Creates queryOptions from PHP-defined dataSource config.
 * Supports both simple format (single query) and batch format (multiple queries).
 * Normalizes batch responses so extractRows/extractMeta work uniformly.
 */

import { queryOptions } from '@tanstack/react-query'

import { type ApiFilters, transformFiltersToApi } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import { type Timeframe,TIMEFRAME_TO_GROUP_BY } from '@/lib/response-helpers'
import { WordPress } from '@/lib/wordpress'

/** Format a Date to ISO datetime string without timezone (YYYY-MM-DDTHH:mm:ss) */
const formatIsoDateTime = (d: Date) => d.toISOString().slice(0, 19)

interface GenericReportParams {
  page: number
  per_page: number
  order_by: string
  order: 'asc' | 'desc'
  date_from: string
  date_to: string
  previous_date_from?: string
  previous_date_to?: string
  filters?: unknown[]
  apiFilters?: ApiFilters
  timeframe?: Timeframe
  /** Query overrides merged into the main query (e.g., group_by override) */
  queryOverrides?: Record<string, unknown>
}

interface GenericReportResponse {
  success: boolean
  data: {
    rows: Record<string, unknown>[]
    total: number
  }
  meta?: {
    date_from: string
    date_to: string
    page: number
    per_page: number
    total_pages: number
    total_rows: number
    preferences?: {
      columns?: string[]
      comparison_columns?: string[]
    }
  }
}

interface BatchResponse {
  success: boolean
  items: Record<string, {
    success: boolean
    data: {
      rows: Record<string, unknown>[]
      totals?: Record<string, unknown>
    }
    meta?: GenericReportResponse['meta']
  }>
}

interface DataSourceConfig {
  sources?: string[]
  group_by?: string[]
  queryId?: string
  queries?: PhpBatchQuery[]
  columnMapping?: Record<string, string>
}

interface ReportQueryConfig {
  context?: string
  defaultApiColumns?: string[]
  /** When set, dates are computed from a rolling window inside queryFn (not from global filters) */
  realtime?: { windowMinutes: number }
}

export function createGenericQueryOptions(
  slug: string,
  dataSource: DataSourceConfig,
  reportConfig?: ReportQueryConfig
) {
  const isBatch = !!(dataSource.queries && dataSource.queries.length > 0)

  return (params: GenericReportParams) => {
    const hasCompare = !!(params.previous_date_from && params.previous_date_to)
    // Map frontend column ID to API sort field
    const mappedOrderBy = dataSource.columnMapping?.[params.order_by] || params.order_by

    // Transform UI filters to API format and merge with custom API filters (short-circuit when empty)
    const hasUiFilters = !!(params.filters && params.filters.length > 0)
    const hasApiFilters = !!(params.apiFilters && Object.keys(params.apiFilters).length > 0)
    const hasFilters = hasUiFilters || hasApiFilters
    const allFilters = hasFilters
      ? {
          ...(hasUiFilters ? transformFiltersToApi(params.filters as Parameters<typeof transformFiltersToApi>[0]) : {}),
          ...params.apiFilters,
        }
      : {}

    if (isBatch) {
      const mainQueryId = dataSource.queryId || dataSource.queries![0].id

      const timeframeGroupBy = TIMEFRAME_TO_GROUP_BY[params.timeframe || 'daily']

      return queryOptions({
        // eslint-disable-next-line @tanstack/query/exhaustive-deps -- dataSource.queries is a static config closed over at factory creation time
        queryKey: [
          slug,
          params.page,
          params.per_page,
          mappedOrderBy,
          params.order,
          params.date_from,
          params.date_to,
          params.previous_date_from,
          params.previous_date_to,
          mainQueryId,
          hasFilters ? allFilters : null,
          params.timeframe || null,
          params.queryOverrides || null,
        ],
        queryFn: async () => {
          // Build batch queries with pagination/sorting injected into the main query
          // For chart-format queries: override group_by based on timeframe, inject compare + comparison dates
          const queries = dataSource.queries!.map((q) => {
            // Inject filters into every sub-query (backend doesn't propagate top-level filters)
            const base = {
              ...q,
              ...(hasFilters && { filters: allFilters }),
            }
            if (q.id === mainQueryId) {
              return {
                ...base,
                page: params.page,
                per_page: params.per_page,
                order_by: mappedOrderBy,
                order: params.order.toUpperCase(),
                compare: q.compare !== undefined ? q.compare : hasCompare,
                ...(reportConfig?.context && { context: reportConfig.context }),
                ...params.queryOverrides,
              }
            }
            // Chart queries: apply timeframe group_by and comparison dates
            if (q.format === 'chart') {
              return {
                ...base,
                group_by: [timeframeGroupBy],
                compare: q.compare !== undefined ? q.compare : hasCompare,
                ...(hasCompare && {
                  previous_date_from: params.previous_date_from,
                  previous_date_to: params.previous_date_to,
                }),
              }
            }
            return base
          })

          const response = await clientRequest.post<BatchResponse>(
            '',
            {
              date_from: params.date_from,
              date_to: params.date_to,
              compare: hasCompare,
              ...(hasCompare && {
                previous_date_from: params.previous_date_from,
                previous_date_to: params.previous_date_to,
              }),
              queries,
            },
            {
              params: {
                action: WordPress.getInstance().getAnalyticsAction(),
              },
            }
          )

          // Normalize: extract main query result into standard shape
          // so extractRows/extractMeta work with default paths.
          // Preserve non-main batch items for slots (e.g., chart) via _batchItems.
          const mainResult = response.data.items[mainQueryId]
          const { [mainQueryId]: _, ...remainingItems } = response.data.items
          if (!mainResult) {
            return { ...response, data: { success: false, data: { rows: [], total: 0 }, meta: undefined, _batchItems: remainingItems } }
          }

          return {
            ...response,
            data: {
              success: mainResult.success,
              data: mainResult.data,
              meta: mainResult.meta,
              _batchItems: remainingItems,
            },
          }
        },
      })
    }

    // Realtime format: compute rolling window dates inside queryFn, exclude dates from cache key
    if (reportConfig?.realtime) {
      const windowMs = reportConfig.realtime.windowMinutes * 60 * 1000

      return queryOptions({
        // eslint-disable-next-line @tanstack/query/exhaustive-deps -- reportConfig.context is a static config closed over at factory creation time
        queryKey: [
          slug,
          params.page,
          params.per_page,
          mappedOrderBy,
          params.order,
          'realtime',
          reportConfig.realtime.windowMinutes,
          dataSource.sources,
          dataSource.group_by,
          hasFilters ? allFilters : null,
          params.queryOverrides || null,
        ],
        queryFn: () => {
          // Compute dates inside queryFn so they're fresh on each refetch
          const now = new Date()
          const dateFrom = new Date(now.getTime() - windowMs)

          return clientRequest.post<GenericReportResponse>(
            '',
            {
              sources: dataSource.sources,
              group_by: dataSource.group_by,
              date_from: formatIsoDateTime(dateFrom),
              date_to: formatIsoDateTime(now),
              compare: false,
              ...(hasFilters && { filters: allFilters }),
              page: params.page,
              per_page: params.per_page,
              order_by: mappedOrderBy,
              order: params.order.toUpperCase(),
              format: 'table',
              show_totals: false,
              ...(reportConfig.context && { context: reportConfig.context }),
              ...params.queryOverrides,
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

    // Simple format (existing behavior)
    return queryOptions({
      // eslint-disable-next-line @tanstack/query/exhaustive-deps -- reportConfig.context is a static config closed over at factory creation time
      queryKey: [
        slug,
        params.page,
        params.per_page,
        mappedOrderBy,
        params.order,
        params.date_from,
        params.date_to,
        params.previous_date_from,
        params.previous_date_to,
        dataSource.sources,
        dataSource.group_by,
        hasFilters ? allFilters : null,
        params.queryOverrides || null,
      ],
      queryFn: () =>
        clientRequest.post<GenericReportResponse>(
          '',
          {
            sources: dataSource.sources,
            group_by: dataSource.group_by,
            date_from: params.date_from,
            date_to: params.date_to,
            compare: hasCompare,
            ...(hasCompare && {
              previous_date_from: params.previous_date_from,
              previous_date_to: params.previous_date_to,
            }),
            ...(hasFilters && { filters: allFilters }),
            page: params.page,
            per_page: params.per_page,
            order_by: mappedOrderBy,
            order: params.order.toUpperCase(),
            format: 'table',
            show_totals: false,
            ...(reportConfig?.context && { context: reportConfig.context }),
            ...params.queryOverrides,
          },
          {
            params: {
              action: WordPress.getInstance().getAnalyticsAction(),
            },
          }
        ),
    })
  }
}
