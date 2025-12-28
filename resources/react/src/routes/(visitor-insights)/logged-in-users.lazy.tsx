import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute, getRouteApi, useNavigate } from '@tanstack/react-router'
import type { ColumnDef, SortingState, VisibilityState } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { Info } from 'lucide-react'
import { useCallback, useEffect, useMemo, useRef, useState } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { DataTableColumnHeaderSortable } from '@/components/custom/data-table-column-header-sortable'
import { DateRangePicker, type DateRange } from '@/components/custom/date-range-picker'
import { type Filter, FilterBar } from '@/components/custom/filter-bar'
import { FilterButton, type FilterField, getOperatorDisplay } from '@/components/custom/filter-button'
import { LineChart } from '@/components/custom/line-chart'
import { Badge } from '@/components/ui/badge'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'
import { formatDateForAPI, formatDecimal } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import type { LoggedInUser as LoggedInUserRecord } from '@/services/visitor-insight/get-logged-in-users'
import { getLoggedInUsersBatchQueryOptions } from '@/services/visitor-insight/get-logged-in-users-batch'
import {
  computeFullVisibility,
  parseColumnPreferences,
  resetUserPreferences,
  saveUserPreferences,
} from '@/services/user-preferences'

const PER_PAGE = 50
const CONTEXT = 'logged_in_users_data_table'
const DEFAULT_HIDDEN_COLUMNS: string[] = []

// Base columns always required for the query (grouping, identification)
const BASE_COLUMNS = ['visitor_id', 'visitor_hash']

// Column dependencies: UI column → API columns needed for that column to render
const COLUMN_DEPENDENCIES: Record<string, string[]> = {
  visitorInfo: [
    'ip_address',
    'country_code',
    'country_name',
    'region_name',
    'city_name',
    'os_name',
    'browser_name',
    'browser_version',
    'user_id',
    'user_login',
    'user_email',
    'user_role',
  ],
  lastVisit: ['last_visit'],
  page: ['entry_page', 'entry_page_title'],
  referrer: ['referrer_domain', 'referrer_channel'],
  entryPage: ['entry_page', 'entry_page_title'],
  totalViews: ['total_views'],
}

// Compute API columns based on visible UI columns and current sort column
// Note: In TanStack Table, columns NOT in visibleColumns are considered visible by default
const computeApiColumns = (
  visibleColumns: Record<string, boolean>,
  allColumnIds: string[],
  sortColumn?: string
): string[] => {
  const apiColumns = new Set<string>(BASE_COLUMNS)

  // For each column, check if it's visible (not explicitly set to false)
  allColumnIds.forEach((columnId) => {
    const isVisible = visibleColumns[columnId] !== false // undefined or true = visible
    if (isVisible && COLUMN_DEPENDENCIES[columnId]) {
      COLUMN_DEPENDENCIES[columnId].forEach((apiCol) => apiColumns.add(apiCol))
    }
  })

  // Always include the sort column's dependencies to ensure ORDER BY works
  if (sortColumn && COLUMN_DEPENDENCIES[sortColumn]) {
    COLUMN_DEPENDENCIES[sortColumn].forEach((apiCol) => apiColumns.add(apiCol))
  }

  return Array.from(apiColumns)
}

// Get visible columns for saving preferences
// Respects columnOrder for ordering, but includes ALL visible columns
const getVisibleColumnsForSave = (
  visibility: Record<string, boolean>,
  columnOrder: string[],
  allColumnIds: string[]
): string[] => {
  // Get all visible column IDs (not explicitly set to false)
  const visibleSet = new Set(allColumnIds.filter((col) => visibility[col] !== false))

  if (columnOrder.length === 0) {
    // No custom order, return all visible columns in default order
    return allColumnIds.filter((col) => visibleSet.has(col))
  }

  // Build result: ordered columns first, then any visible columns not in order
  const result: string[] = []
  const addedSet = new Set<string>()

  // First, add columns from columnOrder that are visible
  for (const col of columnOrder) {
    if (visibleSet.has(col) && !addedSet.has(col)) {
      result.push(col)
      addedSet.add(col)
    }
  }

  // Then add any visible columns not yet in result (maintains their relative order from allColumnIds)
  for (const col of allColumnIds) {
    if (visibleSet.has(col) && !addedSet.has(col)) {
      result.push(col)
      addedSet.add(col)
    }
  }

  return result
}

// Default columns when no preferences are set (all columns visible)
const DEFAULT_API_COLUMNS = [...BASE_COLUMNS, ...Object.values(COLUMN_DEPENDENCIES).flat()].filter(
  (col, index, arr) => arr.indexOf(col) === index
)

// LocalStorage key for caching column preferences
const CACHE_KEY = `wp_statistics_columns_${CONTEXT}`

// Get cached visible columns from localStorage
const getCachedVisibleColumns = (): string[] | null => {
  try {
    const cached = localStorage.getItem(CACHE_KEY)
    if (!cached) return null
    const visibleColumns = JSON.parse(cached) as string[]
    if (!Array.isArray(visibleColumns) || visibleColumns.length === 0) return null
    return visibleColumns
  } catch {
    return null
  }
}

// Get cached API columns from localStorage
const getCachedApiColumns = (allColumnIds: string[]): string[] | null => {
  try {
    const visibleColumns = getCachedVisibleColumns()
    if (!visibleColumns) return null
    // Convert visible UI columns to API columns
    const apiColumns = new Set<string>(BASE_COLUMNS)
    visibleColumns.forEach((columnId) => {
      if (COLUMN_DEPENDENCIES[columnId]) {
        COLUMN_DEPENDENCIES[columnId].forEach((apiCol) => apiColumns.add(apiCol))
      }
    })
    return Array.from(apiColumns)
  } catch {
    return null
  }
}

// Get cached visibility state from localStorage
const getCachedVisibility = (allColumnIds: string[]): VisibilityState | null => {
  try {
    const visibleColumns = getCachedVisibleColumns()
    if (!visibleColumns) return null
    // Build visibility state: columns in cache are visible, others are hidden
    const visibleSet = new Set(visibleColumns)
    const visibility: VisibilityState = {}
    allColumnIds.forEach((col) => {
      visibility[col] = visibleSet.has(col)
    })
    return visibility
  } catch {
    return null
  }
}

// Save visible columns to localStorage cache
const setCachedColumns = (visibleColumns: string[]): void => {
  try {
    localStorage.setItem(CACHE_KEY, JSON.stringify(visibleColumns))
  } catch {
    // Ignore storage errors
  }
}

// Get cached column order from localStorage (same as visible columns order)
const getCachedColumnOrder = (): string[] => {
  return getCachedVisibleColumns() || []
}

export const Route = createLazyFileRoute('/(visitor-insights)/logged-in-users')({
  component: RouteComponent,
})

// Get the route API for accessing validated search params
const routeApi = getRouteApi('/(visitor-insights)/logged-in-users')

interface LoggedInUser {
  id: string
  lastVisit: Date
  country: string
  countryCode: string
  region: string
  city: string
  os: string // lowercase with underscores for icon path
  osName: string // original name for tooltip
  browser: string // lowercase for icon path
  browserName: string // original name for tooltip
  browserVersion: string
  userId: string
  username: string
  email: string
  userRole: string
  referrerDomain?: string
  referrerCategory: string
  entryPage: string
  entryPageTitle: string
  entryPageHasQuery?: boolean
  entryPageQueryString?: string
  page: string
  pageTitle: string
  totalViews: number
}

interface TrafficTrendItem {
  date: string
  userVisitors: number
  userVisitorsPrevious: number
  anonymousVisitors: number
  anonymousVisitorsPrevious: number
  [key: string]: string | number
}

// Format referrer channel for display
const formatReferrerChannel = (channel: string | null | undefined): string => {
  if (!channel) return 'DIRECT TRAFFIC'
  const channelMap: Record<string, string> = {
    direct: 'DIRECT TRAFFIC',
    search: 'SEARCH',
    social: 'SOCIAL',
    referral: 'REFERRAL',
    email: 'EMAIL',
    paid: 'PAID',
  }
  return channelMap[channel.toLowerCase()] || channel.toUpperCase()
}

// Transform API response to component interface
const transformLoggedInUserData = (record: LoggedInUserRecord): LoggedInUser => {
  // Parse entry page for query string
  const entryPageUrl = record.entry_page || '/'
  const hasQueryString = entryPageUrl.includes('?')
  const queryString = hasQueryString ? entryPageUrl.split('?')[1] : undefined

  return {
    id: `user-${record.visitor_id}`,
    lastVisit: new Date(record.last_visit),
    country: record.country_name || 'Unknown',
    countryCode: (record.country_code || '000').toLowerCase(),
    region: record.region_name || '',
    city: record.city_name || '',
    os: (record.os_name || 'unknown').toLowerCase().replace(/\s+/g, '_'),
    osName: record.os_name || 'Unknown',
    browser: (record.browser_name || 'unknown').toLowerCase(),
    browserName: record.browser_name || 'Unknown',
    browserVersion: record.browser_version || '',
    userId: String(record.user_id),
    username: record.user_login || 'user',
    email: record.user_email || '',
    userRole: record.user_role || '',
    referrerDomain: record.referrer_domain || undefined,
    referrerCategory: formatReferrerChannel(record.referrer_channel),
    entryPage: entryPageUrl.split('?')[0] || '/',
    entryPageTitle: record.entry_page_title || record.entry_page || 'Unknown',
    entryPageHasQuery: hasQueryString,
    entryPageQueryString: hasQueryString ? `?${queryString}` : undefined,
    page: record.entry_page || '/',
    pageTitle: record.entry_page_title || record.entry_page || 'Unknown',
    totalViews: record.total_views || 0,
  }
}

const createColumns = (pluginUrl: string): ColumnDef<LoggedInUser>[] => [
  {
    accessorKey: 'visitorInfo',
    header: 'Visitor Info',
    cell: ({ row }) => {
      const user = row.original
      const locationText = `${user.country}, ${user.region}, ${user.city}`

      return (
        <div className="flex items-center gap-2">
          {/* Country Flag */}
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <button className="flex items-center">
                  <img
                    src={`${pluginUrl}public/images/flags/${user.countryCode || '000'}.svg`}
                    alt={user.country}
                    className="w-5 h-5 object-contain"
                  />
                </button>
              </TooltipTrigger>
              <TooltipContent>
                <p>{locationText}</p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>

          {/* OS Icon */}
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <button className="flex items-center">
                  <img
                    src={`${pluginUrl}public/images/operating-system/${user.os}.svg`}
                    alt={user.osName}
                    className="w-4 h-4 object-contain"
                  />
                </button>
              </TooltipTrigger>
              <TooltipContent>
                <p>{user.osName}</p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>

          {/* Browser Icon */}
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <button className="flex items-center">
                  <img
                    src={`${pluginUrl}public/images/browser/${user.browser}.svg`}
                    alt={user.browserName}
                    className="w-4 h-4 object-contain"
                  />
                </button>
              </TooltipTrigger>
              <TooltipContent>
                <p>
                  {user.browserName} {user.browserVersion ? `v${user.browserVersion}` : ''}
                </p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>

          {/* User Badge */}
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <Badge variant="secondary" className="text-xs font-normal">
                  {user.username} #{user.userId}
                </Badge>
              </TooltipTrigger>
              <TooltipContent>
                <p>
                  {user.email || ''} {user.userRole ? `(${user.userRole})` : ''}
                </p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>
        </div>
      )
    },
  },
  {
    accessorKey: 'lastVisit',
    header: ({ column }) => <DataTableColumnHeaderSortable column={column} title="Last Visit" />,
    cell: ({ row }) => {
      const date = row.original.lastVisit
      const formatted = date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
      })
      const time = date.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
      })
      return (
        <div className="whitespace-nowrap">
          {formatted}, {time}
        </div>
      )
    },
  },
  {
    accessorKey: 'page',
    header: 'Page',
    cell: ({ row }) => {
      const user = row.original
      const truncatedTitle = user.pageTitle.length > 35 ? `${user.pageTitle.substring(0, 35)}...` : user.pageTitle

      return (
        <div className="max-w-md inline-flex">
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <span className="cursor-pointer truncate">{truncatedTitle}</span>
              </TooltipTrigger>
              <TooltipContent>
                <p>{user.page}</p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>
        </div>
      )
    },
  },
  {
    accessorKey: 'referrer',
    header: 'Referrer',
    cell: ({ row }) => {
      const user = row.original
      return (
        <div className="inline-flex flex-col items-start gap-1">
          {user.referrerDomain && (
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger asChild>
                  <a
                    href={`https://${user.referrerDomain}`}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="hover:underline max-w-[200px] truncate block"
                  >
                    {user.referrerDomain.length > 25
                      ? `${user.referrerDomain.substring(0, 22)}...${user.referrerDomain.split('.').pop()}`
                      : user.referrerDomain}
                  </a>
                </TooltipTrigger>
                <TooltipContent>
                  <p>https://{user.referrerDomain}</p>
                </TooltipContent>
              </Tooltip>
            </TooltipProvider>
          )}
          <Badge variant="outline" className="text-[8px] text-[#636363] uppercase mt-1">
            {user.referrerCategory}
          </Badge>
        </div>
      )
    },
  },
  {
    accessorKey: 'entryPage',
    header: () => 'Entry Page',
    cell: ({ row }) => {
      const user = row.original
      const truncatedTitle =
        user.entryPageTitle.length > 35 ? `${user.entryPageTitle.substring(0, 35)}...` : user.entryPageTitle

      return (
        <div className="max-w-md inline-flex items-start">
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <div className="flex items-center gap-1 cursor-pointer">
                  <span className="truncate">{truncatedTitle}</span>
                  {user.entryPageHasQuery && <Info className="h-3 w-3 text-[#636363] shrink-0" />}
                </div>
              </TooltipTrigger>
              <TooltipContent>
                {user.entryPageHasQuery && user.entryPageQueryString ? (
                  <p>{user.entryPageQueryString}</p>
                ) : (
                  <p>{user.entryPage}</p>
                )}
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>
        </div>
      )
    },
  },
  {
    accessorKey: 'totalViews',
    header: ({ column }) => (
      <DataTableColumnHeaderSortable column={column} title="Total Views" className="text-right" />
    ),
    cell: ({ row }) => {
      const views = row.original.totalViews
      return (
        <div className="text-right pr-4">
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <span className="cursor-pointer">{views.toLocaleString()}</span>
              </TooltipTrigger>
              <TooltipContent>
                <p>{views.toLocaleString()} Page Views from this user in selected period</p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>
        </div>
      )
    },
  },
]

// Determine group_by based on timeframe
const getGroupBy = (timeframe: 'daily' | 'weekly' | 'monthly'): 'date' | 'week' | 'month' => {
  switch (timeframe) {
    case 'weekly':
      return 'week'
    case 'monthly':
      return 'month'
    default:
      return 'date'
  }
}

// Create default filters with proper operator display labels
const getDefaultFilters = (filterFields: FilterField[]): Filter[] => {
  const field = filterFields.find((f) => f.name === 'logged_in')
  const valueLabel = field?.options?.find((o) => String(o.value) === '1')?.label || 'Logged-in'
  return [
    {
      id: 'logged_in-logged_in-filter-default',
      label: field?.label || 'Login Status',
      operator: getOperatorDisplay('is'),
      rawOperator: 'is',
      value: valueLabel,
      rawValue: '1',
    },
  ]
}

// URL filter format includes displayValue for searchable fields that don't have pre-loaded options
interface UrlFilter {
  field: string
  operator: string
  value: string | string[]
  displayValue?: string // Display label for the value (e.g., "Iran" instead of "5")
}

// Convert URL filter format to Filter type
const urlFiltersToFilters = (urlFilters: UrlFilter[] | undefined, filterFields: FilterField[]): Filter[] => {
  if (!urlFilters || !Array.isArray(urlFilters) || urlFilters.length === 0) return getDefaultFilters(filterFields)

  return urlFilters.map((urlFilter, index) => {
    const field = filterFields.find((f) => f.name === urlFilter.field)
    const label = field?.label || urlFilter.field

    // Use displayValue from URL if available (for searchable fields)
    // Otherwise try to look up from field options, or fall back to raw value
    let displayValue = urlFilter.displayValue
    if (!displayValue) {
      displayValue = Array.isArray(urlFilter.value) ? urlFilter.value.join(', ') : String(urlFilter.value)
      if (field?.options) {
        const values = Array.isArray(urlFilter.value) ? urlFilter.value : [urlFilter.value]
        const labels = values
          .map((v) => field.options?.find((o) => String(o.value) === String(v))?.label || v)
          .join(', ')
        displayValue = labels
      }
    }

    // Create valueLabels from displayValue and rawValue for searchable filters
    // This allows the filter panel to show labels instead of raw values
    let valueLabels: Record<string, string> | undefined
    if (displayValue && urlFilter.value) {
      const values = Array.isArray(urlFilter.value) ? urlFilter.value : [urlFilter.value]
      const displayValues = displayValue.split(', ')
      valueLabels = {}
      values.forEach((v, i) => {
        valueLabels![String(v)] = displayValues[i] || String(v)
      })
    }

    // Create filter ID in the expected format: field-field-filter-restored-index
    const filterId = `${urlFilter.field}-${urlFilter.field}-filter-restored-${index}`

    return {
      id: filterId,
      label,
      operator: getOperatorDisplay(urlFilter.operator as FilterOperator),
      rawOperator: urlFilter.operator,
      value: displayValue,
      rawValue: urlFilter.value,
      valueLabels,
    }
  })
}

// Extract the field name from filter ID
// Filter IDs are in format: "field_name-field_name-filter-..." or "field_name-index"
const extractFilterField = (filterId: string): string => {
  return filterId.split('-')[0]
}

// Convert Filter type to URL filter format
const filtersToUrlFilters = (filters: Filter[]): UrlFilter[] => {
  return filters.map((filter) => ({
    field: extractFilterField(filter.id),
    operator: filter.rawOperator || filter.operator,
    value: filter.rawValue || filter.value,
    // Store display value for searchable fields that don't have pre-loaded options
    displayValue: String(filter.value),
  }))
}

function RouteComponent() {
  const navigate = useNavigate()
  const { filters: urlFilters, page: urlPage } = routeApi.useSearch()

  const [sorting, setSorting] = useState<SortingState>([{ id: 'lastVisit', desc: true }])
  const [timeframe, setTimeframe] = useState<'daily' | 'weekly' | 'monthly'>('daily')
  const lastSyncedFiltersRef = useRef<string | null>(null)
  const [dateRange, setDateRange] = useState<DateRange>({
    from: new Date(),
    to: new Date(),
  })
  const [compareDateRange, setCompareDateRange] = useState<DateRange | undefined>(undefined)

  const wp = WordPress.getInstance()
  const pluginUrl = wp.getPluginUrl()
  const columns = createColumns(pluginUrl)

  // Get filter fields for 'visitors' group from localized data
  const filterFields = useMemo<FilterField[]>(() => {
    return wp.getFilterFieldsByGroup('visitors') as FilterField[]
  }, [wp])

  // Initialize filters state - null until URL sync is complete
  const [appliedFilters, setAppliedFilters] = useState<Filter[] | null>(null)

  // Initialize page state
  const [page, setPage] = useState(1)

  // Sync filters FROM URL on mount (only once)
  useEffect(() => {
    if (lastSyncedFiltersRef.current !== null) return // Already initialized

    const filtersFromUrl = urlFiltersToFilters(urlFilters, filterFields)
    setAppliedFilters(filtersFromUrl)
    setPage(urlPage || 1)
    // Mark as initialized with what the URL actually had (not defaults)
    // This allows sync-to-URL effect to update URL with defaults if URL was empty
    lastSyncedFiltersRef.current = JSON.stringify(urlFilters || [])
  }, [urlFilters, urlPage, filterFields])

  const handleDateRangeUpdate = useCallback((values: { range: DateRange; rangeCompare?: DateRange }) => {
    setDateRange(values.range)
    setCompareDateRange(values.rangeCompare)
    setPage(1)
  }, [])

  // Sync filters TO URL when they change (only after initialization and actual change)
  useEffect(() => {
    if (lastSyncedFiltersRef.current === null || appliedFilters === null) return // Not initialized yet

    const urlFilterData = filtersToUrlFilters(appliedFilters)
    const serialized = JSON.stringify(urlFilterData)

    // Only sync if actually changed
    if (serialized === lastSyncedFiltersRef.current && page === (urlPage || 1)) return

    lastSyncedFiltersRef.current = serialized
    navigate({
      search: (prev) => ({
        ...prev,
        filters: urlFilterData.length > 0 ? urlFilterData : undefined,
        page: page > 1 ? page : undefined,
      }),
      replace: true,
    })
  }, [appliedFilters, page, navigate, urlPage])

  // Get date range for chart (varies by timeframe)
  const chartDateFrom = useMemo(() => {
    const date = new Date()
    if (timeframe === 'monthly') {
      date.setFullYear(date.getFullYear() - 1)
    } else if (timeframe === 'weekly') {
      date.setDate(date.getDate() - 8 * 7) // 8 weeks
    } else {
      date.setDate(date.getDate() - 30) // 30 days
    }
    return date.toISOString().split('T')[0]
  }, [timeframe])

  // Determine sort parameters from sorting state
  const orderBy = sorting.length > 0 ? sorting[0].id : 'lastVisit'
  const order = sorting.length > 0 && sorting[0].desc ? 'desc' : 'asc'

  // Get all hideable column IDs from the columns definition
  const allColumnIds = useMemo(() => {
    return columns.filter((col) => col.enableHiding !== false).map((col) => col.accessorKey as string)
  }, [columns])

  // Track API columns for query optimization (state so changes trigger refetch)
  // Initialize from cache if available, otherwise use all columns
  const [apiColumns, setApiColumns] = useState<string[]>(() => {
    return getCachedApiColumns(allColumnIds) || DEFAULT_API_COLUMNS
  })

  // Fetch all data in a single batch request (only when filters are initialized)
  const {
    data: batchResponse,
    isFetching: isBatchFetching,
    isError: isBatchError,
    error: batchError,
  } = useQuery({
    ...getLoggedInUsersBatchQueryOptions({
      page,
      per_page: PER_PAGE,
      order_by: orderBy,
      order: order as 'asc' | 'desc',
      date_from: formatDateForAPI(dateRange.from),
      date_to: formatDateForAPI(dateRange.to || dateRange.from),
      ...(compareDateRange?.from &&
        compareDateRange?.to && {
          previous_date_from: formatDateForAPI(compareDateRange.from),
          previous_date_to: formatDateForAPI(compareDateRange.to),
        }),
      group_by: getGroupBy(timeframe),
      filters: appliedFilters || [],
      context: CONTEXT,
      columns: apiColumns,
    }),
    placeholderData: keepPreviousData,
    enabled: appliedFilters !== null,
  })

  // Extract individual responses from batch
  const usersResponse = batchResponse?.data?.items?.logged_in_users
  const loggedInTrendsResponse = batchResponse?.data?.items?.logged_in_trends
  const anonymousTrendsResponse = batchResponse?.data?.items?.anonymous_trends

  // Track column order state
  const [columnOrder, setColumnOrder] = useState<string[]>(() => getCachedColumnOrder())

  // Track if preferences have been applied (to prevent re-computation on subsequent API responses)
  const hasAppliedPrefs = useRef(false)
  const computedVisibilityRef = useRef<VisibilityState | null>(null)
  const computedColumnOrderRef = useRef<string[] | null>(null)

  // Track if initial preference sync has been done (to prevent unnecessary refetches)
  const hasInitialPrefSync = useRef(false)

  // Stable empty visibility state to avoid creating new objects on each render
  const emptyVisibilityRef = useRef<VisibilityState>({})

  // Compute initial visibility only once when API returns preferences
  const initialColumnVisibility = useMemo(() => {
    // If we've already computed visibility, return the cached value
    if (hasAppliedPrefs.current && computedVisibilityRef.current) {
      return computedVisibilityRef.current
    }

    // Use cached visibility from localStorage while waiting for API response
    // This prevents flash of all columns before preferences load
    if (!usersResponse) {
      const cachedVisibility = getCachedVisibility(allColumnIds)
      if (cachedVisibility) {
        return cachedVisibility
      }
      return emptyVisibilityRef.current
    }

    const prefs = usersResponse.meta?.preferences?.columns

    // If no preferences in API response (new user or reset), use defaults
    if (!prefs || prefs.length === 0) {
      const defaultVisibility = DEFAULT_HIDDEN_COLUMNS.reduce(
        (acc, col) => ({ ...acc, [col]: false }),
        {} as VisibilityState
      )
      hasAppliedPrefs.current = true
      computedVisibilityRef.current = defaultVisibility
      computedColumnOrderRef.current = []
      return defaultVisibility
    }

    // Parse preferences and compute full visibility
    const { visibleColumnsSet, columnOrder: newOrder } = parseColumnPreferences(prefs)
    const visibility = computeFullVisibility(visibleColumnsSet, allColumnIds)

    // Mark as applied and cache the result
    hasAppliedPrefs.current = true
    computedVisibilityRef.current = visibility
    computedColumnOrderRef.current = newOrder

    return visibility
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [usersResponse, allColumnIds])

  // Sync column order when preferences are computed (only once on initial load)
  useEffect(() => {
    if (hasAppliedPrefs.current && computedVisibilityRef.current && !hasInitialPrefSync.current) {
      hasInitialPrefSync.current = true
      // Sync column order from preferences
      if (computedColumnOrderRef.current && computedColumnOrderRef.current.length > 0) {
        setColumnOrder(computedColumnOrderRef.current)
      }
      // Note: We don't update apiColumns here on initial load because:
      // 1. DEFAULT_API_COLUMNS already includes all columns
      // 2. The initial query already fetched with all columns
      // 3. API column optimization only happens when user changes visibility
    }
  }, [initialColumnVisibility])

  // Track current visibility for save operations (updated via callback)
  const currentVisibilityRef = useRef<VisibilityState>(initialColumnVisibility)

  // Helper to compare two arrays for equality (same elements, same order)
  const arraysEqual = useCallback((a: string[], b: string[]): boolean => {
    if (a.length !== b.length) return false
    return a.every((val, idx) => val === b[idx])
  }, [])

  // Handle column visibility changes (for persistence and query optimization)
  const handleColumnVisibilityChange = useCallback(
    (visibility: VisibilityState) => {
      currentVisibilityRef.current = visibility
      // Use local function that properly handles all visible columns
      const visibleColumns = getVisibleColumnsForSave(visibility, columnOrder, allColumnIds)
      saveUserPreferences({ context: CONTEXT, columns: visibleColumns })
      // Cache visible columns in localStorage for next page load
      setCachedColumns(visibleColumns)
      // Update API columns for optimized queries (include sort column)
      // Use functional update to avoid unnecessary refetches when columns haven't changed
      const currentSortColumn = sorting.length > 0 ? sorting[0].id : 'lastVisit'
      const newApiColumns = computeApiColumns(visibility, allColumnIds, currentSortColumn)
      setApiColumns((prev) => (arraysEqual(prev, newApiColumns) ? prev : newApiColumns))
    },
    [columnOrder, sorting, allColumnIds, arraysEqual]
  )

  // Handle column order changes
  const handleColumnOrderChange = useCallback(
    (order: string[]) => {
      setColumnOrder(order)
      // Use local function that properly handles all visible columns
      const visibleColumns = getVisibleColumnsForSave(currentVisibilityRef.current, order, allColumnIds)
      saveUserPreferences({ context: CONTEXT, columns: visibleColumns })
      // Cache visible columns in localStorage for next page load
      setCachedColumns(visibleColumns)
    },
    [allColumnIds]
  )

  // Handle reset to default
  const handleColumnPreferencesReset = useCallback(() => {
    setColumnOrder([])
    const defaultVisibility = DEFAULT_HIDDEN_COLUMNS.reduce(
      (acc, col) => ({ ...acc, [col]: false }),
      {} as VisibilityState
    )
    computedVisibilityRef.current = defaultVisibility
    currentVisibilityRef.current = defaultVisibility
    // Reset API columns to default (use functional update to avoid unnecessary refetch)
    setApiColumns((prev) => (arraysEqual(prev, DEFAULT_API_COLUMNS) ? prev : DEFAULT_API_COLUMNS))
    resetUserPreferences({ context: CONTEXT })
    // Clear localStorage cache
    try {
      localStorage.removeItem(CACHE_KEY)
    } catch {
      // Ignore storage errors
    }
  }, [arraysEqual])

  // Transform users data
  const tableData = useMemo(() => {
    if (!usersResponse?.data?.rows) return []
    return usersResponse.data.rows.map(transformLoggedInUserData)
  }, [usersResponse])

  // Get pagination info
  const totalRows = usersResponse?.meta?.total_pages ? usersResponse.meta.total_pages * PER_PAGE : tableData.length
  const totalPages = usersResponse?.meta?.total_pages || Math.ceil(totalRows / PER_PAGE) || 1

  // Combine traffic trends data from chart format responses
  const trafficTrendsData = useMemo<TrafficTrendItem[]>(() => {
    // Chart format has labels[] and datasets[{key, label, data, comparison?}]
    const loggedInLabels = loggedInTrendsResponse?.labels || []
    const loggedInDatasets = loggedInTrendsResponse?.datasets || []
    const anonymousLabels = anonymousTrendsResponse?.labels || []
    const anonymousDatasets = anonymousTrendsResponse?.datasets || []

    // Get dataset by key
    const getDataset = (datasets: typeof loggedInDatasets, key: string) =>
      datasets.find((d) => d.key === key)?.data || []

    // Get logged-in visitors data
    const loggedInVisitors = getDataset(loggedInDatasets, 'visitors')
    const loggedInVisitorsPrevious = getDataset(loggedInDatasets, 'visitors_previous')

    // Get anonymous visitors data
    const anonymousVisitors = getDataset(anonymousDatasets, 'visitors')
    const anonymousVisitorsPrevious = getDataset(anonymousDatasets, 'visitors_previous')

    // Use logged-in labels as primary (both should have same labels)
    const labels = loggedInLabels.length > 0 ? loggedInLabels : anonymousLabels

    // Build combined data array
    return labels.map((date, index) => ({
      date,
      userVisitors: Number(loggedInVisitors[index]) || 0,
      userVisitorsPrevious: Number(loggedInVisitorsPrevious[index]) || 0,
      anonymousVisitors: Number(anonymousVisitors[index]) || 0,
      anonymousVisitorsPrevious: Number(anonymousVisitorsPrevious[index]) || 0,
    }))
  }, [loggedInTrendsResponse, anonymousTrendsResponse])

  // Calculate totals for metrics
  const totalUserVisitors = trafficTrendsData.reduce((sum, item) => sum + item.userVisitors, 0)
  const totalUserVisitorsPrevious = trafficTrendsData.reduce((sum, item) => sum + item.userVisitorsPrevious, 0)
  const totalAnonymousVisitors = trafficTrendsData.reduce((sum, item) => sum + item.anonymousVisitors, 0)
  const totalAnonymousVisitorsPrevious = trafficTrendsData.reduce(
    (sum, item) => sum + item.anonymousVisitorsPrevious,
    0
  )

  const trafficTrendsMetrics = [
    {
      key: 'userVisitors',
      label: __('User Visitors', 'wp-statistics'),
      color: 'var(--chart-1)',
      enabled: true,
      value: totalUserVisitors >= 1000 ? `${formatDecimal(totalUserVisitors / 1000)}k` : totalUserVisitors.toString(),
      previousValue:
        totalUserVisitorsPrevious >= 1000
          ? `${formatDecimal(totalUserVisitorsPrevious / 1000)}k`
          : totalUserVisitorsPrevious.toString(),
    },
    {
      key: 'anonymousVisitors',
      label: __('Anonymous Visitors', 'wp-statistics'),
      color: 'var(--chart-4)',
      enabled: true,
      value:
        totalAnonymousVisitors >= 1000
          ? `${formatDecimal(totalAnonymousVisitors / 1000)}k`
          : totalAnonymousVisitors.toString(),
      previousValue:
        totalAnonymousVisitorsPrevious >= 1000
          ? `${formatDecimal(totalAnonymousVisitorsPrevious / 1000)}k`
          : totalAnonymousVisitorsPrevious.toString(),
    },
  ]

  // Handle sorting changes
  const handleSortingChange = useCallback((newSorting: SortingState) => {
    setSorting(newSorting)
    setPage(1) // Reset to first page when sorting changes
  }, [])

  // Handle page changes
  const handlePageChange = useCallback((newPage: number) => {
    setPage(newPage)
  }, [])

  const handleRemoveFilter = (filterId: string) => {
    setAppliedFilters((prev) => (prev ? prev.filter((f) => f.id !== filterId) : []))
    setPage(1) // Reset to first page when filters change
  }

  // Handle filter application with page reset
  const handleApplyFilters = useCallback((filters: Filter[]) => {
    setAppliedFilters(filters)
    setPage(1) // Reset to first page when filters change
  }, [])

  const isChartLoading = isBatchFetching

  return (
    <div className="min-w-0">
      {/* Header row with title and filter button */}
      <div className="flex items-center justify-between p-4 bg-white border-b border-input">
        <h1 className="text-2xl font-medium text-neutral-700">{__('Logged-in Users', 'wp-statistics')}</h1>
        <div className="flex items-center gap-2">
          {filterFields.length > 0 && appliedFilters !== null && (
            <FilterButton fields={filterFields} appliedFilters={appliedFilters} onApplyFilters={handleApplyFilters} />
          )}
          <DateRangePicker
            initialDateFrom={dateRange.from}
            initialDateTo={dateRange.to}
            onUpdate={handleDateRangeUpdate}
            showCompare={true}
            align="end"
          />
        </div>
      </div>

      <div className="p-4 grid gap-6">
        {/* Applied filters row (separate from button) */}
        {appliedFilters && appliedFilters.length > 0 && (
          <FilterBar filters={appliedFilters} onRemoveFilter={handleRemoveFilter} />
        )}

        <LineChart
          title={__('Traffic Trends', 'wp-statistics')}
          data={trafficTrendsData}
          metrics={trafficTrendsMetrics}
          showPreviousPeriod={true}
          timeframe={timeframe}
          onTimeframeChange={setTimeframe}
          isLoading={isChartLoading}
        />

        {isBatchError ? (
          <div className="p-4 text-center">
            <p className="text-red-500">{__('Failed to load logged-in users', 'wp-statistics')}</p>
            <p className="text-sm text-muted-foreground">{batchError?.message}</p>
          </div>
        ) : (
          <DataTable
            title={__('Latest Views', 'wp-statistics')}
            columns={columns}
            data={tableData}
            sorting={sorting}
            onSortingChange={handleSortingChange}
            manualSorting={true}
            manualPagination={true}
            pageCount={totalPages}
            page={page}
            onPageChange={handlePageChange}
            totalRows={totalRows}
            rowLimit={PER_PAGE}
            showColumnManagement={true}
            showPagination={true}
            isFetching={isBatchFetching}
            hiddenColumns={DEFAULT_HIDDEN_COLUMNS}
            initialColumnVisibility={initialColumnVisibility}
            columnOrder={columnOrder.length > 0 ? columnOrder : undefined}
            onColumnVisibilityChange={handleColumnVisibilityChange}
            onColumnOrderChange={handleColumnOrderChange}
            onColumnPreferencesReset={handleColumnPreferencesReset}
            emptyStateMessage={__('No logged-in users found for the selected period', 'wp-statistics')}
          />
        )}
      </div>
    </div>
  )
}
