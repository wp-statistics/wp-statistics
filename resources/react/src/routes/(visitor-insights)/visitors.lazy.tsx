import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute, getRouteApi, useNavigate } from '@tanstack/react-router'
import type { ColumnDef, SortingState, VisibilityState } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { useCallback, useEffect, useMemo, useRef, useState } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { DataTableColumnHeaderSortable } from '@/components/custom/data-table-column-header-sortable'
import { type DateRange,DateRangePicker } from '@/components/custom/date-range-picker'
import { type Filter, FilterBar } from '@/components/custom/filter-bar'
import { FilterButton, type FilterField } from '@/components/custom/filter-button'
import {
  DurationCell,
  JourneyCell,
  LastVisitCell,
  NumericCell,
  ReferrerCell,
  VisitorInfoCell,
  type VisitorInfoConfig,
} from '@/components/data-table-columns'
import { Badge } from '@/components/ui/badge'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'
import { COLUMN_SIZES } from '@/lib/column-sizes'
import {
  clearCachedColumns,
  type ColumnConfig,
  computeApiColumns,
  getCachedApiColumns,
  getCachedVisibility,
  getCachedVisibleColumns,
  getDefaultApiColumns,
  getVisibleColumnsForSave,
  setCachedColumns,
} from '@/lib/column-utils'
import {
  filtersToUrlFilters,
  formatReferrerChannel,
  urlFiltersToFilters,
} from '@/lib/filter-utils'
import { parseEntryPage } from '@/lib/url-utils'
import { formatDateForAPI, formatDecimal,formatDuration } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import {
  computeFullVisibility,
  parseColumnPreferences,
  resetUserPreferences,
  saveUserPreferences,
} from '@/services/user-preferences'
import type { VisitorRecord } from '@/services/visitor-insight/get-visitors'
import { getVisitorsQueryOptions } from '@/services/visitor-insight/get-visitors'

const PER_PAGE = 25
const CONTEXT = 'visitors_data_table'
const DEFAULT_HIDDEN_COLUMNS = ['viewsPerSession', 'bounceRate', 'visitorStatus']

// Column configuration for this page
const COLUMN_CONFIG: ColumnConfig = {
  baseColumns: ['visitor_id', 'visitor_hash'],
  columnDependencies: {
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
    journey: ['entry_page', 'entry_page_title', 'exit_page', 'exit_page_title'],
    totalViews: ['total_views'],
    totalSessions: ['total_sessions'],
    sessionDuration: ['avg_session_duration'],
    viewsPerSession: ['pages_per_session'],
    bounceRate: ['bounce_rate'],
    visitorStatus: ['visitor_status', 'first_visit'],
  },
  context: CONTEXT,
}

// Default columns when no preferences are set (all columns visible)
const DEFAULT_API_COLUMNS = getDefaultApiColumns(COLUMN_CONFIG)

// Get cached column order from localStorage (same as visible columns order)
const getCachedColumnOrder = (): string[] => {
  return getCachedVisibleColumns(CONTEXT) || []
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


// Transform API response to component interface
const transformVisitorData = (record: VisitorRecord): Visitor => {
  const entryPageData = parseEntryPage(record.entry_page, record.entry_page_title)

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
    entryPage: entryPageData.path,
    entryPageTitle: entryPageData.title,
    entryPageHasQuery: entryPageData.hasQueryString,
    entryPageQueryString: entryPageData.queryString,
    utmCampaign: entryPageData.utmCampaign,
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

const createColumns = (config: VisitorInfoConfig): ColumnDef<Visitor>[] => [
  // Primary columns (reordered for importance)
  {
    accessorKey: 'visitorInfo',
    header: () => 'Visitor Info',
    size: COLUMN_SIZES.visitorInfo,
    cell: ({ row }) => {
      const visitor = row.original
      return (
        <VisitorInfoCell
          data={{
            country: {
              code: visitor.countryCode,
              name: visitor.country,
              region: visitor.region,
              city: visitor.city,
            },
            os: { icon: visitor.os, name: visitor.osName },
            browser: { icon: visitor.browser, name: visitor.browserName, version: visitor.browserVersion },
            user: visitor.userId && visitor.username
              ? {
                  id: Number(visitor.userId),
                  username: visitor.username,
                  email: visitor.email,
                  role: visitor.userRole,
                }
              : undefined,
            identifier: visitor.hash || visitor.ipAddress,
          }}
          config={config}
        />
      )
    },
    meta: {
      priority: 'primary',
      cardPosition: 'header',
      mobileLabel: 'Visitor',
    },
  },
  {
    accessorKey: 'lastVisit',
    header: ({ column }) => <DataTableColumnHeaderSortable column={column} title="Last Visit" />,
    size: COLUMN_SIZES.lastVisit,
    cell: ({ row }) => <LastVisitCell date={row.original.lastVisit} />,
    meta: {
      priority: 'primary',
      cardPosition: 'header',
      mobileLabel: 'Last Visit',
    },
  },
  {
    accessorKey: 'totalViews',
    header: ({ column }) => <DataTableColumnHeaderSortable column={column} title="Views" className="text-right" />,
    size: COLUMN_SIZES.views,
    cell: ({ row }) => <NumericCell value={row.original.totalViews} />,
    meta: {
      priority: 'primary',
      cardPosition: 'body',
      mobileLabel: 'Views',
    },
  },
  {
    accessorKey: 'totalSessions',
    header: ({ column }) => <DataTableColumnHeaderSortable column={column} title="Sessions" className="text-right" />,
    size: COLUMN_SIZES.sessions,
    cell: ({ row }) => <NumericCell value={row.original.totalSessions} />,
    meta: {
      priority: 'primary',
      cardPosition: 'body',
      mobileLabel: 'Sessions',
    },
  },
  {
    accessorKey: 'sessionDuration',
    header: ({ column }) => <DataTableColumnHeaderSortable column={column} title="Duration" className="text-right" />,
    size: COLUMN_SIZES.duration,
    cell: ({ row }) => <DurationCell seconds={row.original.sessionDuration} />,
    meta: {
      priority: 'primary',
      cardPosition: 'body',
      mobileLabel: 'Duration',
    },
  },
  // Secondary columns
  {
    accessorKey: 'referrer',
    header: () => 'Referrer',
    size: COLUMN_SIZES.referrer,
    cell: ({ row }) => (
      <ReferrerCell
        data={{
          domain: row.original.referrerDomain,
          category: row.original.referrerCategory,
        }}
      />
    ),
    meta: {
      priority: 'secondary',
      mobileLabel: 'Referrer',
    },
  },
  {
    accessorKey: 'journey',
    header: () => 'Journey',
    size: COLUMN_SIZES.journey,
    cell: ({ row }) => {
      const visitor = row.original
      const isBounce = visitor.entryPage === visitor.exitPage
      return (
        <JourneyCell
          data={{
            entryPage: {
              title: visitor.entryPageTitle,
              url: visitor.entryPage,
              utmCampaign: visitor.utmCampaign,
            },
            exitPage: {
              title: visitor.exitPageTitle,
              url: visitor.exitPage,
            },
            isBounce,
          }}
        />
      )
    },
    meta: {
      priority: 'secondary',
      mobileLabel: 'Journey',
    },
  },
  // Hidden by default columns
  {
    accessorKey: 'viewsPerSession',
    header: ({ column }) => <DataTableColumnHeaderSortable column={column} title="Per Session" className="text-right" />,
    size: COLUMN_SIZES.viewsPerSession,
    enableHiding: true,
    cell: ({ row }) => <NumericCell value={row.original.viewsPerSession} decimals={1} />,
    meta: {
      priority: 'secondary',
      mobileLabel: 'Per Session',
    },
  },
  {
    accessorKey: 'bounceRate',
    header: ({ column }) => <DataTableColumnHeaderSortable column={column} title="Bounce" className="text-right" />,
    size: COLUMN_SIZES.bounceRate,
    enableHiding: true,
    cell: ({ row }) => <NumericCell value={row.original.bounceRate} suffix="%" />,
    meta: {
      priority: 'secondary',
      mobileLabel: 'Bounce',
    },
  },
  {
    accessorKey: 'visitorStatus',
    header: () => 'Status',
    size: COLUMN_SIZES.status,
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
              {isNew ? `First visit ${firstVisitDate}` : `Since ${firstVisitDate}`}
            </TooltipContent>
          </Tooltip>
        </TooltipProvider>
      )
    },
    meta: {
      priority: 'secondary',
      mobileLabel: 'Status',
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

  // Initialize filters state - null until URL sync is complete
  const [appliedFilters, setAppliedFilters] = useState<Filter[] | null>(null)

  // Initialize page state
  const [page, setPage] = useState(1)

  // Sync filters FROM URL on mount (only once)
  useEffect(() => {
    if (lastSyncedFiltersRef.current !== null) return // Already initialized

    // Parse URL filters - urlFilters comes from validated search params
    const filtersFromUrl = urlFiltersToFilters(urlFilters, filterFields)

    setAppliedFilters(filtersFromUrl)
    if (urlPage && urlPage > 1) {
      setPage(urlPage)
    }
    // Mark as initialized with the current filter state
    lastSyncedFiltersRef.current = JSON.stringify(filtersToUrlFilters(filtersFromUrl))
  }, [urlFilters, urlPage, filterFields])

  // Date range state (default to last 30 days)
  const [dateRange, setDateRange] = useState<DateRange>(() => {
    const today = new Date()
    const thirtyDaysAgo = new Date()
    thirtyDaysAgo.setDate(today.getDate() - 29)
    return {
      from: thirtyDaysAgo,
      to: today,
    }
  })

  // Compare date range state (off by default)
  const [compareDateRange, setCompareDateRange] = useState<DateRange | undefined>(undefined)

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
  const [columnOrder, setColumnOrder] = useState<string[]>(() => getCachedColumnOrder())

  // Track API columns for query optimization (state so changes trigger refetch)
  // Initialize from cache if available, otherwise use all columns
  const [apiColumns, setApiColumns] = useState<string[]>(() => {
    return getCachedApiColumns(allColumnIds, COLUMN_CONFIG) || DEFAULT_API_COLUMNS
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

  // Fetch data from API (only when filters are initialized)
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
      filters: appliedFilters || [],
      context: CONTEXT,
      columns: apiColumns,
    }),
    placeholderData: keepPreviousData,
    enabled: appliedFilters !== null,
  })

  // Compute initial visibility only once when API returns preferences
  const initialColumnVisibility = useMemo(() => {
    // If we've already computed visibility, return the cached value
    if (hasAppliedPrefs.current && computedVisibilityRef.current) {
      return computedVisibilityRef.current
    }

    // Use cached visibility from localStorage while waiting for API response
    // This prevents flash of all columns before preferences load
    if (!response?.data) {
      const cachedVisibility = getCachedVisibility(CONTEXT, allColumnIds)
      if (cachedVisibility) {
        return cachedVisibility
      }
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
      setCachedColumns(CONTEXT, visibleColumns)
      // Update API columns for optimized queries (include sort column)
      // Use functional update to avoid unnecessary refetches when columns haven't changed
      const currentSortColumn = sorting.length > 0 ? sorting[0].id : 'lastVisit'
      const newApiColumns = computeApiColumns(visibility, allColumnIds, COLUMN_CONFIG, currentSortColumn)
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
      setCachedColumns(CONTEXT, visibleColumns)
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
    clearCachedColumns(CONTEXT)
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
    setAppliedFilters((prev) => (prev ? prev.filter((f) => f.id !== filterId) : []))
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
      <div className="flex items-center justify-between px-4 py-3 bg-white border-b border-input">
        <h1 className="text-xl font-semibold text-neutral-800">{__('Visitors', 'wp-statistics')}</h1>
        <div className="flex items-center gap-3">
          {filterFields.length > 0 && appliedFilters !== null && (
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

      <div className="p-2">
        {/* Applied filters row (separate from button) */}
        {appliedFilters && appliedFilters.length > 0 && (
          <FilterBar filters={appliedFilters} onRemoveFilter={handleRemoveFilter} className="mb-2" />
        )}

        {isError ? (
          <div className="p-2 text-center">
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
