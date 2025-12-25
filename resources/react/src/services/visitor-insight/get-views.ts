import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { clientRequest } from '@/lib/client-request'

export interface ViewRecord {
  visitor_id: number
  visitor_hash: string
  ip_address: string
  last_visit: string
  total_views: number
  total_sessions: number
  country_code: string
  country_name: string
  region_name: string
  city_name: string
  os_name: string
  browser_name: string
  browser_version: string | null
  device_type_name: string
  user_id: number | null
  user_login: string | null
  user_email: string | null
  user_role: string | null
  referrer_domain: string | null
  referrer_channel: string
  entry_page: string
  entry_page_title: string
}

export interface GetViewsResponse {
  success: boolean
  data: {
    rows: ViewRecord[]
    total: number
    totals?: {
      visitors: number
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

// API filter format: { filter_key: { operator: value } }
export type ApiFilters = Record<string, Record<string, string | string[]>>

export interface GetViewsParams {
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
}

// Extract the field name from filter ID
// Filter IDs are in format: "os-os-filter-1766484171552-9509610" where the first segment is the field name
const extractFilterKey = (filterId: string): string => {
  // Split by hyphen and take the first segment (the actual field name)
  return filterId.split('-')[0]
}

// Transform UI filters to API format
// UI: { id, label, operator, rawOperator, value, rawValue }
// API: { filter_key: { operator: value } }
const transformFiltersToApi = (filters: Filter[]): ApiFilters => {
  const apiFilters: ApiFilters = {}

  for (const filter of filters) {
    // Extract the field name from the filter id (e.g., 'os' from 'os-os-filter-...')
    const filterKey = extractFilterKey(filter.id)
    // Use rawOperator if available, otherwise fall back to operator
    const operator = filter.rawOperator || filter.operator
    // Use rawValue if available, otherwise fall back to value
    // Convert number to string since API expects string | string[]
    const rawValue = filter.rawValue ?? filter.value
    const value: string | string[] = Array.isArray(rawValue)
      ? rawValue
      : typeof rawValue === 'number'
        ? String(rawValue)
        : rawValue

    apiFilters[filterKey] = {
      [operator]: value,
    }
  }

  return apiFilters
}

// Map frontend column names to API column names
const columnMapping: Record<string, string> = {
  visitorInfo: 'visitor_id',
  lastVisit: 'last_visit',
  page: 'entry_page',
  referrer: 'referrer_domain',
  entryPage: 'entry_page',
  totalViews: 'total_views',
}

export const getViewsQueryOptions = ({
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
}: GetViewsParams) => {
  // Map frontend column name to API column name
  const apiOrderBy = columnMapping[order_by] || order_by
  // Transform UI filters to API format
  const apiFilters = transformFiltersToApi(filters)
  // Check if compare dates are provided (must be boolean, not the date value)
  const hasCompare = !!(previous_date_from && previous_date_to)

  return queryOptions({
    queryKey: ['views', page, per_page, apiOrderBy, order, date_from, date_to, previous_date_from, previous_date_to, apiFilters, context],
    queryFn: () =>
      clientRequest.post<GetViewsResponse>(
        '',
        {
          sources: ['visitors'],
          group_by: ['visitor'],
          columns: [
            'visitor_id',
            'visitor_hash',
            'ip_address',
            'last_visit',
            'total_views',
            'total_sessions',
            'country_code',
            'country_name',
            'region_name',
            'city_name',
            'os_name',
            'browser_name',
            'browser_version',
            'device_type_name',
            'user_id',
            'user_login',
            'user_email',
            'user_role',
            'referrer_domain',
            'referrer_channel',
            'entry_page',
            'entry_page_title',
          ],
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
            action: 'wp_statistics_analytics',
          },
        }
      ),
  })
}
