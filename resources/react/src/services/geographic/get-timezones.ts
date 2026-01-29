import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { transformFiltersToApi } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import { createListParams, type QueryFilter,queryKeys } from '@/lib/query-keys'
import { WordPress } from '@/lib/wordpress'

/**
 * API response record for a single timezone
 */
export interface TimezoneRecord {
  timezone_id: number
  timezone_name: string
  timezone_offset: string
  timezone_is_dst: number
  visitors: number
  views: number
  previous?: {
    visitors?: number
    views?: number
  }
}

/**
 * Table query result structure
 */
export interface TimezonesTableResult {
  success: boolean
  data: {
    rows: TimezoneRecord[]
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

/**
 * Batch response structure for timezones query
 */
export interface GetTimezonesResponse {
  success: boolean
  items: {
    timezones?: TimezonesTableResult
  }
  errors?: Record<string, { code: string; message: string }>
  skipped?: string[]
}

export interface GetTimezonesParams {
  page: number
  per_page: number
  order_by: string
  order: 'asc' | 'desc'
  date_from: string
  date_to: string
  previous_date_from?: string
  previous_date_to?: string
  filters?: Filter[]
  context?: string
  columns?: string[]
  queryFilters?: QueryFilter[]
}

// Map frontend column names to API column names
const columnMapping: Record<string, string> = {
  timezone: 'timezone_name',
  visitors: 'visitors',
  views: 'views',
}

// Default columns when no specific columns are provided
const DEFAULT_COLUMNS = [
  'timezone_id',
  'timezone_name',
  'timezone_offset',
  'timezone_is_dst',
  'visitors',
  'views',
]

export const getTimezonesQueryOptions = ({
  page,
  per_page,
  order_by,
  order,
  date_from,
  date_to,
  previous_date_from,
  previous_date_to,
  filters = [],
  context,
  columns,
  queryFilters,
}: GetTimezonesParams) => {
  const apiOrderBy = columnMapping[order_by] || order_by
  const apiFilters = transformFiltersToApi(filters)
  const hasCompare = !!(previous_date_from && previous_date_to)
  const apiColumns = columns && columns.length > 0 ? columns : DEFAULT_COLUMNS

  return queryOptions({
    // eslint-disable-next-line @tanstack/query/exhaustive-deps -- hasCompare is derived from compareDateFrom/compareDateTo which are in the key
    queryKey: queryKeys.geographic.timezonesList(
      createListParams(date_from, date_to, page, per_page, apiOrderBy, order, {
        compareDateFrom: previous_date_from,
        compareDateTo: previous_date_to,
        filters: apiFilters,
        context,
        columns: apiColumns,
        queryFilters,
      })
    ),
    queryFn: () =>
      clientRequest.post<GetTimezonesResponse>(
        '',
        {
          date_from,
          date_to,
          compare: hasCompare,
          ...(hasCompare && {
            previous_date_from,
            previous_date_to,
          }),
          ...(Object.keys(apiFilters).length > 0 && { filters: apiFilters }),
          queries: [
            {
              id: 'timezones',
              sources: ['visitors', 'views'],
              group_by: ['timezone'],
              columns: apiColumns,
              page,
              per_page,
              order_by: apiOrderBy,
              order: order.toUpperCase(),
              format: 'table',
              compare: hasCompare,
              ...(context && { context }),
              ...(queryFilters?.length && { filters: queryFilters }),
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
 * Helper to extract timezones data from batch response
 */
export function extractTimezonesData(response: { data?: GetTimezonesResponse } | undefined): {
  rows: TimezoneRecord[]
  meta: { totalRows: number; totalPages: number; page: number } | null
} {
  const timezonesResult = response?.data?.items?.timezones

  if (!timezonesResult?.success) {
    return { rows: [], meta: null }
  }

  const rows = timezonesResult.data?.rows || []

  const meta = timezonesResult.meta
    ? {
        totalRows: Number(timezonesResult.meta.total_rows) || 0,
        totalPages: Number(timezonesResult.meta.total_pages) || 1,
        page: Number(timezonesResult.meta.page) || 1,
      }
    : null

  return { rows, meta }
}
