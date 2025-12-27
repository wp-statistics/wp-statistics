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
import { FilterButton, type FilterField } from '@/components/custom/filter-button'
import { Badge } from '@/components/ui/badge'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'
import { formatDateForAPI, formatDuration } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import type { VisitorRecord } from '@/services/visitor-insight/get-visitors'
import { getVisitorsQueryOptions } from '@/services/visitor-insight/get-visitors'
import {
  computeFullVisibility,
  parseColumnPreferences,
  resetUserPreferences,
  saveUserPreferences,
} from '@/services/user-preferences'

const PER_PAGE = 25
const CONTEXT = 'visitors_data_table'
const DEFAULT_HIDDEN_COLUMNS = ['viewsPerSession', 'bounceRate', 'visitorStatus']

// Base columns always required for the query (grouping, identification)
const BASE_COLUMNS = ['visitor_id', 'visitor_hash']

// Column dependencies: UI column → API columns needed for that column to render
const COLUMN_DEPENDENCIES: Record<string, string[]> = {
  lastVisit: ['last_visit'],
  visitorInfo: [
    'ip_address',
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
const DEFAULT_API_COLUMNS = [
  ...BASE_COLUMNS,
  ...Object.values(COLUMN_DEPENDENCIES).flat(),
].filter((col, index, arr) => arr.indexOf(col) === index)

// LocalStorage key for caching column preferences
const CACHE_KEY = `wp_statistics_columns_${CONTEXT}`

// Get cached API columns from localStorage
const getCachedApiColumns = (allColumnIds: string[]): string[] | null => {
  try {
    const cached = localStorage.getItem(CACHE_KEY)
    if (!cached) return null
    const visibleColumns = JSON.parse(cached) as string[]
    if (!Array.isArray(visibleColumns) || visibleColumns.length === 0) return null
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

// Save visible columns to localStorage cache
const setCachedColumns = (visibleColumns: string[]): void => {
  try {
    localStorage.setItem(CACHE_KEY, JSON.stringify(visibleColumns))
  } catch {
    // Ignore storage errors
  }
}

export const Route = createLazyFileRoute('/(visitor-insights)/visitors')({
  component: RouteComponent,
})

// Get the route API for accessing validated search params
const routeApi = getRouteApi('/(visitor-insights)/visitors')

interface Visitor {
  id: string
  lastVisit: Date
  firstVisit: Date
  country: string
  countryCode: string
  region: string
  city: string
  os: string // lowercase with underscores for icon path
  osName: string // original name for tooltip
  browser: string // lowercase for icon path
  browserName: string // original name for tooltip
  browserVersion: string
  userId?: string
  username?: string
  email?: string
  userRole?: string
  ipAddress?: string
  hash?: string
  referrerDomain?: string
  referrerCategory: string
  entryPage: string
  entryPageTitle: string
  entryPageHasQuery?: boolean
  entryPageQueryString?: string
  utmCampaign?: string
  exitPage: string
  exitPageTitle: string
  totalViews: number
  totalSessions: number
  sessionDuration: number // in seconds
  viewsPerSession: number
  bounceRate: number
  visitorStatus: 'new' | 'returning'
}

interface VisitorInfoColumnConfig {
  pluginUrl: string
  trackLoggedInEnabled: boolean
  hashEnabled: boolean
}

// Transform API response to component interface
const transformVisitorData = (record: VisitorRecord): Visitor => {
  // Parse entry page for query string
  const entryPageUrl = record.entry_page || '/'
  const hasQueryString = entryPageUrl.includes('?')
  const queryString = hasQueryString ? entryPageUrl.split('?')[1] : undefined

  // Extract UTM campaign if present
  let utmCampaign: string | undefined
  if (queryString) {
    const params = new URLSearchParams(queryString)
    utmCampaign = params.get('utm_campaign') || undefined
  }

  return {
    id: `visitor-${record.visitor_id}`,
    lastVisit: new Date(record.last_visit),
    firstVisit: record.first_visit ? new Date(record.first_visit) : new Date(record.last_visit),
    country: record.country_name || 'Unknown',
    countryCode: (record.country_code || '000').toLowerCase(),
    region: record.region_name || '',
    city: record.city_name || '',
    os: (record.os_name || 'unknown').toLowerCase().replace(/\s+/g, '_'),
    osName: record.os_name || 'Unknown',
    browser: (record.browser_name || 'unknown').toLowerCase(),
    browserName: record.browser_name || 'Unknown',
    browserVersion: record.browser_version || '',
    userId: record.user_id ? String(record.user_id) : undefined,
    username: record.user_login || undefined,
    email: record.user_email || undefined,
    userRole: record.user_role || undefined,
    ipAddress: record.ip_address || undefined,
    hash: record.visitor_hash || undefined,
    referrerDomain: record.referrer_domain || undefined,
    referrerCategory: formatReferrerChannel(record.referrer_channel),
    entryPage: entryPageUrl.split('?')[0] || '/',
    entryPageTitle: record.entry_page_title || record.entry_page || 'Unknown',
    entryPageHasQuery: hasQueryString,
    entryPageQueryString: hasQueryString ? `?${queryString}` : undefined,
    utmCampaign,
    exitPage: record.exit_page || '/',
    exitPageTitle: record.exit_page_title || record.exit_page || 'Unknown',
    totalViews: Number(record.total_views) || 0,
    totalSessions: Number(record.total_sessions) || 0,
    sessionDuration: Math.round(Number(record.avg_session_duration) || 0),
    viewsPerSession: Number(record.pages_per_session) || 0,
    bounceRate: Math.round(Number(record.bounce_rate) || 0),
    visitorStatus: record.visitor_status || 'returning',
  }
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

// Convert URL filter format to Filter type
const urlFiltersToFilters = (
  urlFilters: Array<{ field: string; operator: string; value: string | string[] }> | undefined,
  filterFields: FilterField[]
): Filter[] => {
  if (!urlFilters || !Array.isArray(urlFilters) || urlFilters.length === 0) return []

  return urlFilters.map((urlFilter, index) => {
    const field = filterFields.find((f) => f.name === urlFilter.field)
    const label = field?.label || urlFilter.field

    // Get display value from field options if available
    let displayValue = Array.isArray(urlFilter.value) ? urlFilter.value.join(', ') : urlFilter.value
    if (field?.options) {
      const values = Array.isArray(urlFilter.value) ? urlFilter.value : [urlFilter.value]
      const labels = values.map((v) => field.options?.find((o) => String(o.value) === v)?.label || v).join(', ')
      displayValue = labels
    }

    // Create filter ID in the expected format: field-field-filter-restored-index
    const filterId = `${urlFilter.field}-${urlFilter.field}-filter-restored-${index}`

    return {
      id: filterId,
      label,
      operator: urlFilter.operator,
      rawOperator: urlFilter.operator,
      value: displayValue,
      rawValue: urlFilter.value,
    }
  })
}

// Extract the field name from filter ID
// Filter IDs are in format: "field_name-field_name-filter-..." or "field_name-index"
const extractFilterField = (filterId: string): string => {
  return filterId.split('-')[0]
}

// Convert Filter type to URL filter format
const filtersToUrlFilters = (
  filters: Filter[]
): Array<{ field: string; operator: string; value: string | string[] }> => {
  return filters.map((filter) => ({
    field: extractFilterField(filter.id),
    operator: filter.rawOperator || filter.operator,
    value: filter.rawValue || filter.value,
  }))
}

const createColumns = (config: VisitorInfoColumnConfig): ColumnDef<Visitor>[] => [
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
    accessorKey: 'visitorInfo',
    header: () => 'Visitor Info',
    cell: ({ row }) => {
      const visitor = row.original
      const locationText = `${visitor.country}, ${visitor.region}, ${visitor.city}`

      // Determine what to show for identifier based on settings
      // Show user badge only if: trackLoggedInEnabled AND user_id exists
      const showUserBadge = config.trackLoggedInEnabled && visitor.userId
      // Show hash/IP only when user badge is not shown
      // Format hash display: strip #hash# prefix and show first 6 chars
      const formatHashDisplay = (value: string): string => {
        const cleanHash = value.replace(/^#hash#/i, '')
        return cleanHash.substring(0, 6)
      }
      // Determine identifier display based on settings and available data
      const getIdentifierDisplay = (): string | undefined => {
        if (config.hashEnabled) {
          // hashEnabled = true → show first 6 chars of hash
          if (visitor.hash) return formatHashDisplay(visitor.hash)
          if (visitor.ipAddress?.startsWith('#hash#')) return formatHashDisplay(visitor.ipAddress)
        }
        // hashEnabled = false → show full IP address
        return visitor.ipAddress
      }
      const identifierDisplay = getIdentifierDisplay()

      return (
        <div className="flex items-center gap-2">
          {/* Country Flag */}
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <button className="flex items-center">
                  <img
                    src={`${config.pluginUrl}public/images/flags/${visitor.countryCode || '000'}.svg`}
                    alt={visitor.country}
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
                    src={`${config.pluginUrl}public/images/operating-system/${visitor.os}.svg`}
                    alt={visitor.osName}
                    className="w-4 h-4 object-contain"
                  />
                </button>
              </TooltipTrigger>
              <TooltipContent>
                <p>{visitor.osName}</p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>

          {/* Browser Icon */}
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <button className="flex items-center">
                  <img
                    src={`${config.pluginUrl}public/images/browser/${visitor.browser}.svg`}
                    alt={visitor.browserName}
                    className="w-4 h-4 object-contain"
                  />
                </button>
              </TooltipTrigger>
              <TooltipContent>
                <p>
                  {visitor.browserName} {visitor.browserVersion ? `v${visitor.browserVersion}` : ''}
                </p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>

          {/* User Badge (only if trackLoggedInEnabled AND user_id exists) */}
          {showUserBadge ? (
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger asChild>
                  <Badge variant="secondary" className="text-xs font-normal">
                    {visitor.username} #{visitor.userId}
                  </Badge>
                </TooltipTrigger>
                <TooltipContent>
                  <p>
                    {visitor.email || ''} {visitor.userRole ? `(${visitor.userRole})` : ''}
                  </p>
                </TooltipContent>
              </Tooltip>
            </TooltipProvider>
          ) : (
            /* IP or Hash (only when user badge is not shown) */
            identifierDisplay && (
              <span className="text-xs text-muted-foreground font-mono">{identifierDisplay}</span>
            )
          )}
        </div>
      )
    },
  },
  {
    accessorKey: 'referrer',
    header: () => 'Referrer',
    cell: ({ row }) => {
      const visitor = row.original
      return (
        <div className="flex flex-col gap-1 items-start">
          {visitor.referrerDomain && (
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger asChild>
                  <a
                    href={`https://${visitor.referrerDomain}`}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="hover:underline max-w-[200px] truncate block"
                  >
                    {visitor.referrerDomain.length > 25
                      ? `${visitor.referrerDomain.substring(0, 22)}...${visitor.referrerDomain.split('.').pop()}`
                      : visitor.referrerDomain}
                  </a>
                </TooltipTrigger>
                <TooltipContent>
                  <p>https://{visitor.referrerDomain}</p>
                </TooltipContent>
              </Tooltip>
            </TooltipProvider>
          )}
          <Badge variant="outline" className="text-[8px] text-[#636363] uppercase mt-1">
            {visitor.referrerCategory}
          </Badge>
        </div>
      )
    },
  },
  {
    accessorKey: 'entryPage',
    header: () => 'Entry Page',
    cell: ({ row }) => {
      const visitor = row.original
      const truncatedTitle =
        visitor.entryPageTitle.length > 35 ? `${visitor.entryPageTitle.substring(0, 35)}...` : visitor.entryPageTitle

      return (
        <div className="max-w-md inline-flex flex-col items-start">
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <div className="flex items-center gap-1 cursor-pointer">
                  <span className="truncate">{truncatedTitle}</span>
                  {visitor.entryPageHasQuery && <Info className="h-3 w-3 text-[#636363] shrink-0" />}
                </div>
              </TooltipTrigger>
              <TooltipContent>
                {visitor.entryPageHasQuery && visitor.entryPageQueryString ? (
                  <p>{visitor.entryPageQueryString}</p>
                ) : (
                  <p>{visitor.entryPage}</p>
                )}
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>
          {visitor.utmCampaign && (
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger asChild>
                  <div className="text-[9px] text-[#636363] mt-1 cursor-pointer">{visitor.utmCampaign}</div>
                </TooltipTrigger>
                <TooltipContent>
                  <p>Campaign: {visitor.utmCampaign}</p>
                </TooltipContent>
              </Tooltip>
            </TooltipProvider>
          )}
        </div>
      )
    },
  },
  {
    accessorKey: 'exitPage',
    header: () => 'Exit Page',
    cell: ({ row }) => {
      const visitor = row.original
      const truncatedTitle =
        visitor.exitPageTitle.length > 35 ? `${visitor.exitPageTitle.substring(0, 35)}...` : visitor.exitPageTitle

      return (
        <div className="max-w-md inline-flex flex-col items-start">
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <span className="cursor-pointer truncate">{truncatedTitle}</span>
              </TooltipTrigger>
              <TooltipContent>
                <p>{visitor.exitPage}</p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>
        </div>
      )
    },
  },
  {
    accessorKey: 'totalViews',
    header: ({ column }) => <DataTableColumnHeaderSortable column={column} title="Total Views" />,
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
                <p>{views.toLocaleString()} Page Views from this visitor in selected period</p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>
        </div>
      )
    },
  },
  {
    accessorKey: 'totalSessions',
    header: ({ column }) => <DataTableColumnHeaderSortable column={column} title="Total Sessions" />,
    cell: ({ row }) => {
      const sessions = row.original.totalSessions
      return (
        <div className="text-right pr-4">
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <span className="cursor-pointer">{sessions.toLocaleString()}</span>
              </TooltipTrigger>
              <TooltipContent>
                <p>
                  {sessions.toLocaleString()} {sessions === 1 ? 'session' : 'sessions'} in selected period
                </p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>
        </div>
      )
    },
  },
  {
    accessorKey: 'sessionDuration',
    header: ({ column }) => <DataTableColumnHeaderSortable column={column} title="Session Duration" />,
    cell: ({ row }) => {
      const seconds = row.original.sessionDuration
      const formatted = formatDuration(seconds)

      return (
        <div className="text-right pr-4">
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <span className="cursor-pointer">{formatted}</span>
              </TooltipTrigger>
              <TooltipContent>
                <p>Average session duration: {formatted}</p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>
        </div>
      )
    },
  },
  {
    accessorKey: 'viewsPerSession',
    header: ({ column }) => <DataTableColumnHeaderSortable column={column} title="Views Per Session" />,
    enableHiding: true,
    cell: ({ row }) => {
      const value = row.original.viewsPerSession
      return (
        <div className="text-right pr-4">
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <span className="cursor-pointer">{value.toFixed(1)}</span>
              </TooltipTrigger>
              <TooltipContent>
                <p>{value.toFixed(1)} average page views per session</p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>
        </div>
      )
    },
  },
  {
    accessorKey: 'bounceRate',
    header: ({ column }) => <DataTableColumnHeaderSortable column={column} title="Bounce Rate" />,
    enableHiding: true,
    cell: ({ row }) => {
      const rate = row.original.bounceRate
      return (
        <div className="text-right pr-4">
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <span className="cursor-pointer">{rate}%</span>
              </TooltipTrigger>
              <TooltipContent>
                <p>{rate}% of sessions viewed only one page</p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>
        </div>
      )
    },
  },
  {
    accessorKey: 'visitorStatus',
    header: () => 'Status',
    enableHiding: true,
    cell: ({ row }) => {
      const visitor = row.original
      const isNew = visitor.visitorStatus === 'new'
      const firstVisitDate = visitor.firstVisit.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
      })

      return (
        <TooltipProvider>
          <Tooltip>
            <TooltipTrigger asChild>
              <Badge variant={isNew ? 'default' : 'secondary'} className="text-xs font-normal capitalize">
                {visitor.visitorStatus}
              </Badge>
            </TooltipTrigger>
            <TooltipContent>
              <p>{isNew ? `First visit: ${firstVisitDate}` : `Returning visitor since ${firstVisitDate}`}</p>
            </TooltipContent>
          </Tooltip>
        </TooltipProvider>
      )
    },
  },
]

function RouteComponent() {
  const navigate = useNavigate()
  const { filters: urlFilters, page: urlPage } = routeApi.useSearch()

  const [sorting, setSorting] = useState<SortingState>([{ id: 'lastVisit', desc: true }])
  const lastSyncedFiltersRef = useRef<string | null>(null)

  const wp = WordPress.getInstance()
  const pluginUrl = wp.getPluginUrl()
  const columns = useMemo(
    () =>
      createColumns({
        pluginUrl,
        trackLoggedInEnabled: wp.isTrackLoggedInEnabled(),
        hashEnabled: wp.isHashEnabled(),
      }),
    [pluginUrl, wp]
  )

  // Get filter fields for 'visitors' group from localized data
  const filterFields = useMemo<FilterField[]>(() => {
    return wp.getFilterFieldsByGroup('visitors') as FilterField[]
  }, [wp])

  // Initialize filters state
  const [appliedFilters, setAppliedFilters] = useState<Filter[]>([])

  // Initialize page state
  const [page, setPage] = useState(1)

  // Sync filters FROM URL on mount (only once)
  useEffect(() => {
    if (lastSyncedFiltersRef.current !== null) return // Already initialized

    // Parse URL filters - urlFilters comes from validated search params
    const filtersFromUrl = urlFiltersToFilters(urlFilters, filterFields)

    // Only set state if there are actual filters to apply
    if (filtersFromUrl.length > 0) {
      setAppliedFilters(filtersFromUrl)
    }
    if (urlPage && urlPage > 1) {
      setPage(urlPage)
    }
    // Mark as initialized with the current filter state
    lastSyncedFiltersRef.current = JSON.stringify(filtersToUrlFilters(filtersFromUrl))
  }, [urlFilters, urlPage, filterFields])

  // Date range state (default to today)
  const [dateRange, setDateRange] = useState<DateRange>(() => {
    const today = new Date()
    return {
      from: today,
      to: today,
    }
  })

  // Compare date range state (off by default)
  const [compareDateRange, setCompareDateRange] = useState<DateRange | undefined>(undefined)

  // Sync filters TO URL when they change (only after initialization and actual change)
  useEffect(() => {
    if (lastSyncedFiltersRef.current === null) return // Not initialized yet

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

  const handleDateRangeUpdate = useCallback((values: { range: DateRange; rangeCompare?: DateRange }) => {
    setDateRange(values.range)
    setCompareDateRange(values.rangeCompare)
    setPage(1) // Reset to first page when date range changes
  }, [])

  // Determine sort parameters from sorting state
  const orderBy = sorting.length > 0 ? sorting[0].id : 'lastVisit'
  const order = sorting.length > 0 && sorting[0].desc ? 'desc' : 'asc'

  // Get all hideable column IDs from the columns definition
  const allColumnIds = useMemo(() => {
    return columns.filter((col) => col.enableHiding !== false).map((col) => col.accessorKey as string)
  }, [columns])

  // Track column order state
  const [columnOrder, setColumnOrder] = useState<string[]>([])

  // Track API columns for query optimization (state so changes trigger refetch)
  // Initialize from cache if available, otherwise use all columns
  const [apiColumns, setApiColumns] = useState<string[]>(() => {
    return getCachedApiColumns(allColumnIds) || DEFAULT_API_COLUMNS
  })

  // Track if preferences have been applied (to prevent re-computation on subsequent API responses)
  const hasAppliedPrefs = useRef(false)
  const computedVisibilityRef = useRef<VisibilityState | null>(null)
  const computedColumnOrderRef = useRef<string[] | null>(null)

  // Track current visibility for save operations (updated via callback)
  const currentVisibilityRef = useRef<VisibilityState>({})

  // Track if initial preference sync has been done (to prevent unnecessary refetches)
  const hasInitialPrefSync = useRef(false)

  // Stable empty visibility state to avoid creating new objects on each render
  const emptyVisibilityRef = useRef<VisibilityState>({})

  // Fetch data from API
  const {
    data: response,
    isFetching,
    isError,
    error,
  } = useQuery({
    ...getVisitorsQueryOptions({
      page,
      per_page: PER_PAGE,
      order_by: orderBy,
      order: order as 'asc' | 'desc',
      date_from: formatDateForAPI(dateRange.from),
      date_to: formatDateForAPI(dateRange.to || dateRange.from),
      previous_date_from: compareDateRange ? formatDateForAPI(compareDateRange.from) : undefined,
      previous_date_to: compareDateRange ? formatDateForAPI(compareDateRange.to || compareDateRange.from) : undefined,
      filters: appliedFilters,
      context: CONTEXT,
      columns: apiColumns,
    }),
    placeholderData: keepPreviousData,
  })

  // Compute initial visibility only once when API returns preferences
  const initialColumnVisibility = useMemo(() => {
    // If we've already computed visibility, return the cached value
    if (hasAppliedPrefs.current && computedVisibilityRef.current) {
      return computedVisibilityRef.current
    }

    // Wait for API response before computing visibility
    // Return stable reference to avoid triggering effects
    if (!response?.data) {
      return emptyVisibilityRef.current
    }

    const prefs = response.data.meta?.preferences?.columns

    // If no preferences in API response (new user or reset), use defaults
    if (!prefs || prefs.length === 0) {
      const defaultVisibility = DEFAULT_HIDDEN_COLUMNS.reduce(
        (acc, col) => ({ ...acc, [col]: false }),
        {} as VisibilityState
      )
      hasAppliedPrefs.current = true
      computedVisibilityRef.current = defaultVisibility
      currentVisibilityRef.current = defaultVisibility
      computedColumnOrderRef.current = []
      return defaultVisibility
    }

    // Parse preferences and compute full visibility
    const { visibleColumnsSet, columnOrder: newOrder } = parseColumnPreferences(prefs)
    const visibility = computeFullVisibility(visibleColumnsSet, allColumnIds)

    // Mark as applied and cache the result
    hasAppliedPrefs.current = true
    computedVisibilityRef.current = visibility
    currentVisibilityRef.current = visibility
    computedColumnOrderRef.current = newOrder

    return visibility
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [response?.data, allColumnIds])

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
    // Reset visibility to defaults
    const defaultVisibility = DEFAULT_HIDDEN_COLUMNS.reduce(
      (acc, col) => ({ ...acc, [col]: false }),
      {} as VisibilityState
    )
    computedVisibilityRef.current = defaultVisibility
    currentVisibilityRef.current = defaultVisibility
    // Reset API columns to default (use functional update to avoid unnecessary refetch)
    setApiColumns((prev) => (arraysEqual(prev, DEFAULT_API_COLUMNS) ? prev : DEFAULT_API_COLUMNS))
    // Reset preferences on backend
    resetUserPreferences({ context: CONTEXT })
    // Clear localStorage cache
    try {
      localStorage.removeItem(CACHE_KEY)
    } catch {
      // Ignore storage errors
    }
  }, [arraysEqual])

  // Transform API data to component interface
  const tableData = useMemo(() => {
    if (!response?.data?.data?.rows) return []
    return response.data.data.rows.map(transformVisitorData)
  }, [response])

  // Get pagination info from meta
  const totalRows = response?.data?.meta?.total_rows ?? 0
  const totalPages = response?.data?.meta?.total_pages || Math.ceil(totalRows / PER_PAGE) || 1

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
    setAppliedFilters((prev) => prev.filter((f) => f.id !== filterId))
    setPage(1) // Reset to first page when filters change
  }

  // Handle filter application with page reset
  const handleApplyFilters = useCallback((filters: Filter[]) => {
    setAppliedFilters(filters)
    setPage(1) // Reset to first page when filters change
  }, [])

  return (
    <div className="min-w-0">
      {/* Header row with title, filter button, and date picker */}
      <div className="flex items-center justify-between p-4 bg-white border-b border-input">
        <h1 className="text-2xl font-medium text-neutral-700">{__('Visitors', 'wp-statistics')}</h1>
        <div className="flex items-center gap-2">
          {filterFields.length > 0 && (
            <FilterButton fields={filterFields} appliedFilters={appliedFilters} onApplyFilters={handleApplyFilters} />
          )}
          <DateRangePicker
            initialDateFrom={dateRange.from}
            initialDateTo={dateRange.to}
            showCompare={true}
            onUpdate={handleDateRangeUpdate}
            align="end"
          />
        </div>
      </div>

      <div className="p-4">
        {/* Applied filters row (separate from button) */}
        {appliedFilters.length > 0 && (
          <FilterBar filters={appliedFilters} onRemoveFilter={handleRemoveFilter} className="mb-4" />
        )}

        {isError ? (
          <div className="p-4 text-center">
            <p className="text-red-500">{__('Failed to load visitors', 'wp-statistics')}</p>
            <p className="text-sm text-muted-foreground">{error?.message}</p>
          </div>
        ) : (
          <DataTable
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
            isFetching={isFetching}
            hiddenColumns={DEFAULT_HIDDEN_COLUMNS}
            initialColumnVisibility={initialColumnVisibility}
            columnOrder={columnOrder.length > 0 ? columnOrder : undefined}
            onColumnVisibilityChange={handleColumnVisibilityChange}
            onColumnOrderChange={handleColumnOrderChange}
            onColumnPreferencesReset={handleColumnPreferencesReset}
            emptyStateMessage={__('No visitors found for the selected period', 'wp-statistics')}
          />
        )}
      </div>
    </div>
  )
}
