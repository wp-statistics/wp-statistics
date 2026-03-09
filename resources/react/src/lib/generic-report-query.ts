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
import { WordPress } from '@/lib/wordpress'

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
        ],
        queryFn: async () => {
          // Build batch queries with pagination/sorting injected into the main query
          const queries = dataSource.queries!.map((q) => {
            if (q.id === mainQueryId) {
              return {
                ...q,
                page: params.page,
                per_page: params.per_page,
                order_by: mappedOrderBy,
                order: params.order.toUpperCase(),
                compare: q.compare !== undefined ? q.compare : hasCompare,
                ...(reportConfig?.context && { context: reportConfig.context }),
                ...(reportConfig?.defaultApiColumns && { columns: reportConfig.defaultApiColumns }),
              }
            }
            return q
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
              ...(hasFilters && { filters: allFilters }),
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
