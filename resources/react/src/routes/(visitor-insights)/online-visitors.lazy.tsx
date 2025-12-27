import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import type { ColumnDef, SortingState, VisibilityState } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { Info } from 'lucide-react'
import { useCallback, useEffect, useMemo, useRef, useState } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { DataTableColumnHeaderSortable } from '@/components/custom/data-table-column-header-sortable'
import { Badge } from '@/components/ui/badge'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'
import { formatDuration } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import type { OnlineVisitor as APIOnlineVisitor } from '@/services/visitor-insight/get-online-visitors'
import { getOnlineVisitorsQueryOptions } from '@/services/visitor-insight/get-online-visitors'
import {
  computeFullVisibility,
  parseColumnPreferences,
  resetUserPreferences,
  saveUserPreferences,
} from '@/services/user-preferences'

const CONTEXT = 'online_visitors_data_table'
const DEFAULT_HIDDEN_COLUMNS: string[] = []

// Base columns always required for the query (grouping, identification)
const BASE_COLUMNS = ['visitor_id', 'visitor_hash']

// Column dependencies: UI column → API columns needed
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
  onlineFor: ['total_sessions'],
  page: ['entry_page'],
  totalViews: ['total_views'],
  entryPage: ['entry_page'],
  referrer: ['referrer_domain', 'referrer_channel'],
  lastVisit: ['last_visit'],
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

export const Route = createLazyFileRoute('/(visitor-insights)/online-visitors')({
  component: RouteComponent,
})

interface OnlineVisitor {
  id: string
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
  onlineFor: number // in seconds
  page: string
  pageTitle: string
  totalViews: number
  entryPage: string
  entryPageTitle: string
  entryPageHasQuery?: boolean
  entryPageQueryString?: string
  referrerDomain?: string
  referrerCategory: string
  lastVisit: Date
}

interface VisitorInfoColumnConfig {
  pluginUrl: string
  trackLoggedInEnabled: boolean
  hashEnabled: boolean
}

// Transform API response to component interface
const transformVisitorData = (apiVisitor: APIOnlineVisitor): OnlineVisitor => {
  const lastVisitDate = new Date(apiVisitor.last_visit)
  // Calculate online duration based on total_sessions (approximate)
  const onlineForSeconds = Math.max(0, apiVisitor.total_sessions * 60) // Rough estimate

  // Extract query string from entry page if present
  const entryPageUrl = apiVisitor.entry_page || ''
  const hasQuery = entryPageUrl.includes('?')
  const queryString = hasQuery ? entryPageUrl.split('?')[1] : undefined
  const entryPagePath = hasQuery ? entryPageUrl.split('?')[0] : entryPageUrl

  // Extract page title from entry page path
  const getPageTitle = (path: string): string => {
    if (!path || path === '/') return 'Home'
    const segments = path.split('/').filter(Boolean)
    const lastSegment = segments[segments.length - 1] || ''
    return lastSegment.replace(/-/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase())
  }

  return {
    id: `visitor-${apiVisitor.visitor_id}`,
    country: apiVisitor.country_name || 'Unknown',
    countryCode: (apiVisitor.country_code || '000').toLowerCase(),
    region: apiVisitor.region_name || '',
    city: apiVisitor.city_name || '',
    os: (apiVisitor.os_name || 'unknown').toLowerCase().replace(/\s+/g, '_'),
    osName: apiVisitor.os_name || 'Unknown',
    browser: (apiVisitor.browser_name || 'unknown').toLowerCase(),
    browserName: apiVisitor.browser_name || 'Unknown',
    browserVersion: apiVisitor.browser_version || '',
    userId: apiVisitor.user_id ? String(apiVisitor.user_id) : undefined,
    username: apiVisitor.user_login || undefined,
    email: apiVisitor.user_email || undefined,
    userRole: apiVisitor.user_role || undefined,
    ipAddress: apiVisitor.ip_address || undefined,
    hash: apiVisitor.visitor_hash || undefined,
    onlineFor: onlineForSeconds,
    page: entryPagePath || '/',
    pageTitle: getPageTitle(entryPagePath),
    totalViews: apiVisitor.total_views || 0,
    entryPage: entryPagePath || '/',
    entryPageTitle: getPageTitle(entryPagePath),
    entryPageHasQuery: hasQuery,
    entryPageQueryString: queryString ? `?${queryString}` : undefined,
    referrerDomain: apiVisitor.referrer_domain || undefined,
    referrerCategory: formatReferrerChannel(apiVisitor.referrer_channel),
    lastVisit: lastVisitDate,
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

const createColumns = (config: VisitorInfoColumnConfig): ColumnDef<OnlineVisitor>[] => [
  {
    accessorKey: 'visitorInfo',
    header: 'Visitor Info',
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
    accessorKey: 'onlineFor',
    header: ({ column }) => <DataTableColumnHeaderSortable column={column} title="Online For" />,
    cell: ({ row }) => {
      const seconds = row.original.onlineFor
      return <span>{formatDuration(seconds)}</span>
    },
  },
  {
    accessorKey: 'page',
    header: 'Page',
    cell: ({ row }) => {
      const visitor = row.original
      const truncatedTitle =
        visitor.pageTitle.length > 35 ? `${visitor.pageTitle.substring(0, 35)}...` : visitor.pageTitle

      return (
        <div className="max-w-md">
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <span className="cursor-pointer truncate">{truncatedTitle}</span>
              </TooltipTrigger>
              <TooltipContent>
                <p>{visitor.page}</p>
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
                <p>{views.toLocaleString()} Page Views in current session</p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>
        </div>
      )
    },
  },
  {
    accessorKey: 'entryPage',
    header: 'Entry Page',
    cell: ({ row }) => {
      const visitor = row.original
      const truncatedTitle =
        visitor.entryPageTitle.length > 35 ? `${visitor.entryPageTitle.substring(0, 35)}...` : visitor.entryPageTitle

      return (
        <div className="max-w-md inline-flex items-start">
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
        </div>
      )
    },
  },
  {
    accessorKey: 'referrer',
    header: 'Referrer',
    cell: ({ row }) => {
      const visitor = row.original
      return (
        <div className="inline-flex flex-col items-start gap-1">
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
]

const PER_PAGE = 50

function RouteComponent() {
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

  // Pagination state
  const [page, setPage] = useState(1)

  // Sorting state
  const [sorting, setSorting] = useState<SortingState>([{ id: 'lastVisit', desc: true }])

  // Extract sort parameters from sorting state
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

  const {
    data: response,
    isError,
    error,
    isFetching,
  } = useQuery({
    ...getOnlineVisitorsQueryOptions({
      page,
      per_page: PER_PAGE,
      order_by: orderBy,
      order: order as 'asc' | 'desc',
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
    const defaultVisibility = DEFAULT_HIDDEN_COLUMNS.reduce(
      (acc, col) => ({ ...acc, [col]: false }),
      {} as VisibilityState
    )
    computedVisibilityRef.current = defaultVisibility
    currentVisibilityRef.current = defaultVisibility
    // Reset to default API columns (use functional update to avoid unnecessary refetch)
    setApiColumns((prev) => (arraysEqual(prev, DEFAULT_API_COLUMNS) ? prev : DEFAULT_API_COLUMNS))
    resetUserPreferences({ context: CONTEXT })
    // Clear localStorage cache
    try {
      localStorage.removeItem(CACHE_KEY)
    } catch {
      // Ignore storage errors
    }
  }, [arraysEqual])

  // Transform API data to component format
  const visitors = response?.data?.data?.rows?.map(transformVisitorData) || []
  const total = response?.data?.meta?.total_rows ?? 0
  const totalPages = response?.data?.meta?.total_pages || Math.ceil(total / PER_PAGE) || 1

  // Handle sorting change
  const handleSortingChange = useCallback((newSorting: SortingState) => {
    setSorting(newSorting)
    setPage(1) // Reset to first page when sorting changes
  }, [])

  // Handle page change
  const handlePageChange = useCallback((newPage: number) => {
    setPage(newPage)
  }, [])

  return (
    <div className="min-w-0">
      <div className="flex items-center justify-between p-4 bg-white border-b border-input">
        <h1 className="text-2xl font-medium text-neutral-700">{__('Online Visitors', 'wp-statistics')}</h1>
      </div>

      <div className="p-4">
        {isError ? (
          <div className="p-4 text-center">
            <p className="text-red-500">{__('Failed to load online visitors', 'wp-statistics')}</p>
            <p className="text-sm text-muted-foreground">{error?.message}</p>
          </div>
        ) : (
          <DataTable
            columns={columns}
            data={visitors}
            sorting={sorting}
            onSortingChange={handleSortingChange}
            manualSorting={true}
            manualPagination={true}
            pageCount={totalPages}
            page={page}
            onPageChange={handlePageChange}
            totalRows={total}
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
            emptyStateMessage={__('No visitors are currently online', 'wp-statistics')}
          />
        )}
      </div>
    </div>
  )
}
