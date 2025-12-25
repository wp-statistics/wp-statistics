import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { clientRequest } from '@/lib/client-request'

export interface VisitorRecord {
  visitor_id: number
  visitor_hash: string
  ip_address: string | null
  user_id: number | null
  user_login: string | null
  last_visit: string
  country_code: string | null
  country_name: string | null
  region_name: string | null
  city_name: string | null
  os_name: string | null
  browser_name: string | null
  device_type_name: string | null
  referrer_domain: string | null
  referrer_channel: string | null
  entry_page: string | null
  entry_page_title: string | null
  exit_page: string | null
  exit_page_title: string | null
  total_views: number
  total_sessions: number
  avg_session_duration: number | null
  pages_per_session: number | null
  bounce_rate: number | null
  visitor_status: 'new' | 'returning' | null
  first_visit: string | null
}

export interface GetVisitorsResponse {
  success: boolean
  data: {
    rows: VisitorRecord[]
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
      columns: string[] // Array of visible column IDs in their display order
      updated_at: string
    }
  }
}

// API filter format: { filter_key: { operator: value } }
export type ApiFilters = Record<string, Record<string, string | string[]>>

export interface GetVisitorsParams {
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

// Map frontend column names to API column names (for sorting)
const columnMapping: Record<string, string> = {
  lastVisit: 'last_visit',
  visitorInfo: 'visitor_id',
  totalViews: 'total_views',
  totalSessions: 'total_sessions',
  sessionDuration: 'avg_session_duration',
  viewsPerSession: 'pages_per_session',
  bounceRate: 'bounce_rate',
  entryPage: 'entry_page',
  exitPage: 'exit_page',
  referrer: 'referrer_domain',
  visitorStatus: 'visitor_status',
}

// Map each frontend column to its required API columns
// This includes both the main column and any dependent columns needed for formatting
const COLUMN_DEPENDENCIES: Record<string, string[]> = {
  lastVisit: ['last_visit'],
  visitorInfo: [
    'visitor_id',
    'visitor_hash',
    'ip_address',
    'user_id',
    'user_login',
    'country_code',
    'country_name',
    'region_name',
    'city_name',
    'os_name',
    'browser_name',
    'device_type_name',
  ],
  referrer: ['referrer_domain', 'referrer_channel'],
  entryPage: ['entry_page', 'entry_page_title'],
  exitPage: ['exit_page', 'exit_page_title'],
  totalViews: ['total_views'],
  totalSessions: ['total_sessions'],
  sessionDuration: ['avg_session_duration'],
  viewsPerSession: ['pages_per_session'],
  bounceRate: ['bounce_rate'],
  visitorStatus: ['visitor_status', 'first_visit'],
}

// Expand frontend column names to their required API columns
const expandColumnsToApi = (frontendColumns: string[]): string[] => {
  const apiColumns = new Set<string>()

  for (const col of frontendColumns) {
    const deps = COLUMN_DEPENDENCIES[col]
    if (deps) {
      deps.forEach((dep) => apiColumns.add(dep))
    }
  }

  return Array.from(apiColumns)
}

// Default columns to fetch when no user preferences are specified
// Note: first_visit is NOT included here - it will be added via COLUMN_DEPENDENCIES when visitorStatus is visible
const DEFAULT_COLUMNS = [
  'visitor_id',
  'visitor_hash',
  'ip_address',
  'user_id',
  'user_login',
  'last_visit',
  'country_code',
  'country_name',
  'region_name',
  'city_name',
  'os_name',
  'browser_name',
  'device_type_name',
  'referrer_domain',
  'referrer_channel',
  'entry_page',
  'entry_page_title',
  'exit_page',
  'exit_page_title',
  'total_views',
  'total_sessions',
  'avg_session_duration',
  'pages_per_session',
  'bounce_rate',
  'visitor_status',
]

export const getVisitorsQueryOptions = ({
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
}: GetVisitorsParams) => {
  // Map frontend column name to API column name
  const apiOrderBy = columnMapping[order_by] || order_by
  // Transform UI filters to API format
  const apiFilters = transformFiltersToApi(filters)
  // Check if compare dates are provided (must be boolean, not the date value)
  const hasCompare = !!(previous_date_from && previous_date_to)
  // Expand frontend columns to API columns, or use defaults
  // columns param contains frontend names like ['lastVisit', 'visitorInfo', ...]
  // We need to expand these to API names with their dependencies
  const apiColumns = columns && columns.length > 0 ? expandColumnsToApi(columns) : DEFAULT_COLUMNS

  return queryOptions({
    queryKey: [
      'visitors',
      page,
      per_page,
      apiOrderBy,
      order,
      date_from,
      date_to,
      previous_date_from,
      previous_date_to,
      apiFilters,
      context,
    ],
    queryFn: () =>
      clientRequest.post<GetVisitorsResponse>(
        '',
        {
          sources: ['visitors', 'avg_session_duration', 'bounce_rate', 'pages_per_session', 'visitor_status'],
          group_by: ['visitor'],
          columns: [...apiColumns, 'first_visit'],
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
          show_totals: false,
          ...(Object.keys(apiFilters).length > 0 && { filters: apiFilters }),
          ...(context && { context }),
        },
        {
          params: {
            action: 'wp_statistics_analytics',
          },
        }
      ),
  })
}
