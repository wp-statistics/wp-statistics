import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { type ApiFilters, transformFiltersToApi } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import { createListParams, queryKeys } from '@/lib/query-keys'
import { WordPress } from '@/lib/wordpress'

// Re-export ApiFilters for backward compatibility
export type { ApiFilters }

/**
 * API response record for a single referrer
 */
export interface ReferrerRecord {
  referrer_id: number
  referrer_domain: string        // "google.com"
  referrer_name: string          // "Google" (from source-channels.json)
  referrer_channel: string       // "search", "social", "referral", etc.
  visitors: number
  views: number
  avg_session_duration: number
  bounce_rate: number
  pages_per_session: number
  previous?: {
    visitors?: number
    views?: number
    avg_session_duration?: number
    bounce_rate?: number
    pages_per_session?: number
  }
}

export interface GetReferrersResponse {
  success: boolean
  data: {
    rows: ReferrerRecord[]
    total: number
    totals?: {
      visitors: number
      views: number
    }
  }
  meta?: {
    date_from: string
    date_to: string
    page: number
    per_page: number
    total_pages: number
    total_rows: number
    preferences?: {
      columns: string[]
    }
  }
}

export interface GetReferrersParams {
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
}

// Map frontend column names to API column names
const columnMapping: Record<string, string> = {
  domain: 'referrer_domain',
  name: 'referrer_name',
  channel: 'referrer_channel',
  visitors: 'visitors',
  views: 'views',
  sessionDuration: 'avg_session_duration',
  bounceRate: 'bounce_rate',
  pagesPerSession: 'pages_per_session',
}

// Default columns when no specific columns are provided
const DEFAULT_COLUMNS = [
  'referrer_id',
  'referrer_domain',
  'referrer_name',
  'referrer_channel',
  'visitors',
  'views',
  'avg_session_duration',
  'bounce_rate',
  'pages_per_session',
]

export const getReferrersQueryOptions = ({
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
}: GetReferrersParams) => {
  // Map frontend column name to API column name
  const apiOrderBy = columnMapping[order_by] || order_by
  // Transform UI filters to API format
  const apiFilters = transformFiltersToApi(filters)
  // Check if compare dates are provided
  const hasCompare = !!(previous_date_from && previous_date_to)
  // Use provided columns or default to all columns
  const apiColumns = columns && columns.length > 0 ? columns : DEFAULT_COLUMNS

  return queryOptions({
    // eslint-disable-next-line @tanstack/query/exhaustive-deps -- hasCompare is derived from compareDateFrom/compareDateTo which are in the key
    queryKey: queryKeys.referrals.list(
      createListParams(date_from, date_to, page, per_page, apiOrderBy, order, {
        compareDateFrom: previous_date_from,
        compareDateTo: previous_date_to,
        filters: apiFilters,
        context,
        columns: apiColumns,
      })
    ),
    queryFn: () =>
      clientRequest.post<GetReferrersResponse>(
        '',
        {
          sources: ['visitors', 'views', 'avg_session_duration', 'bounce_rate', 'pages_per_session'],
          group_by: ['referrer'],
          date_from,
          date_to,
          compare: hasCompare,
          ...(hasCompare && {
            previous_date_from,
            previous_date_to,
          }),
          page,
          per_page,
          order_by: apiOrderBy,
          order: order.toUpperCase(),
          format: 'table',
          ...(context && { context }),
          show_totals: false,
          ...(Object.keys(apiFilters).length > 0 && { filters: apiFilters }),
        },
        {
          params: {
            action: WordPress.getInstance().getAnalyticsAction(),
          },
        }
      ),
  })
}
