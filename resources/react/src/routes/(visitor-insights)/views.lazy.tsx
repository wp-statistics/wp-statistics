import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute, getRouteApi, useNavigate } from '@tanstack/react-router'
import type { ColumnDef, SortingState, VisibilityState } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { useCallback, useEffect, useMemo, useRef, useState } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { DataTableColumnHeaderSortable } from '@/components/custom/data-table-column-header-sortable'
import { DateRangePicker, type DateRange } from '@/components/custom/date-range-picker'
import { type Filter, FilterBar } from '@/components/custom/filter-bar'
import { FilterButton, type FilterField } from '@/components/custom/filter-button'
import {
  EntryPageCell,
  LastVisitCell,
  NumericCell,
  PageCell,
  ReferrerCell,
  VisitorInfoCell,
  type VisitorInfoConfig,
} from '@/components/data-table-columns'
import {
  type ColumnConfig,
  clearCachedColumns,
  computeApiColumns,
  getCachedApiColumns,
  getCachedVisibility,
  getCachedVisibleColumns,
  getDefaultApiColumns,
  getVisibleColumnsForSave,
  setCachedColumns,
} from '@/lib/column-utils'
import { filtersToUrlFilters, urlFiltersToFilters } from '@/lib/filter-utils'
import { parseEntryPage } from '@/lib/url-utils'
import { COLUMN_SIZES } from '@/lib/column-sizes'
import { formatDateForAPI } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import type { ViewRecord } from '@/services/visitor-insight/get-views'
import { getViewsQueryOptions } from '@/services/visitor-insight/get-views'
import {
  computeFullVisibility,
  parseColumnPreferences,
  resetUserPreferences,
  saveUserPreferences,
} from '@/services/user-preferences'

const PER_PAGE = 50
const CONTEXT = 'views_data_table'
const DEFAULT_HIDDEN_COLUMNS: string[] = []

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
      'user_id',
      'user_login',
      'user_email',
      'user_role',
    ],
    page: ['entry_page', 'entry_page_title'],
    referrer: ['referrer_domain', 'referrer_channel'],
    entryPage: ['entry_page', 'entry_page_title'],
    totalViews: ['total_views'],
  },
  context: CONTEXT,
}

// Default columns when no preferences are set (all columns visible)
const DEFAULT_API_COLUMNS = getDefaultApiColumns(COLUMN_CONFIG)

// Get cached column order from localStorage (same as visible columns order)
const getCachedColumnOrder = (): string[] => {
  return getCachedVisibleColumns(CONTEXT) || []
}

export const Route = createLazyFileRoute('/(visitor-insights)/views')({
  component: RouteComponent,
})

// Get the route API for accessing validated search params
const routeApi = getRouteApi('/(visitor-insights)/views')

type ViewData = {
  lastVisit: string
  visitorInfo: {
    country: { code: string; name: string; region: string; city: string }
    os: { icon: string; name: string }
    browser: { icon: string; name: string; version: string }
    user?: { username: string; id: number; email: string; role: string }
    ipAddress?: string
    hash?: string
  }
  page: {
    title: string
    url: string
  }
  referrer: {
    domain?: string
    fullUrl?: string
    category: string
  }
  entryPage: {
    title: string
    url: string
    hasQueryString: boolean
    queryString?: string
    utmCampaign?: string
  }
  totalViews: number
}

const createColumns = (config: VisitorInfoConfig): ColumnDef<ViewData>[] => [
  {
    accessorKey: 'lastVisit',
    header: ({ column }) => <DataTableColumnHeaderSortable column={column} title="Last Visit" />,
    size: COLUMN_SIZES.lastVisit,
    cell: ({ row }) => <LastVisitCell date={new Date(row.getValue('lastVisit'))} />,
    meta: {
      priority: 'primary',
      cardPosition: 'header',
      mobileLabel: 'Last Visit',
    },
  },
  {
    accessorKey: 'visitorInfo',
    header: 'Visitor Info',
    size: COLUMN_SIZES.visitorInfo,
    cell: ({ row }) => {
      const visitorInfo = row.getValue('visitorInfo') as ViewData['visitorInfo']
      return (
        <VisitorInfoCell
          data={{
            country: {
              code: visitorInfo.country.code,
              name: visitorInfo.country.name,
              region: visitorInfo.country.region,
              city: visitorInfo.country.city,
            },
            os: { icon: visitorInfo.os.icon, name: visitorInfo.os.name },
            browser: { icon: visitorInfo.browser.icon, name: visitorInfo.browser.name, version: visitorInfo.browser.version },
            user: visitorInfo.user
              ? {
                  id: visitorInfo.user.id,
                  username: visitorInfo.user.username,
                  email: visitorInfo.user.email,
                  role: visitorInfo.user.role,
                }
              : undefined,
            identifier: visitorInfo.hash || visitorInfo.ipAddress,
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
    accessorKey: 'page',
    header: ({ column }) => <DataTableColumnHeaderSortable column={column} title="Page" />,
    size: COLUMN_SIZES.page,
    cell: ({ row }) => {
      const page = row.getValue('page') as ViewData['page']
      return <PageCell data={{ title: page.title, url: page.url }} maxLength={35} />
    },
    meta: {
      priority: 'primary',
      cardPosition: 'body',
      mobileLabel: 'Page',
    },
  },
  {
    accessorKey: 'referrer',
    header: 'Referrer',
    size: COLUMN_SIZES.referrer,
    cell: ({ row }) => {
      const referrer = row.getValue('referrer') as ViewData['referrer']
      return (
        <ReferrerCell
          data={{
            domain: referrer.domain,
            category: referrer.category,
          }}
          maxLength={25}
        />
      )
    },
    meta: {
      priority: 'secondary',
      mobileLabel: 'Referrer',
    },
  },
  {
    accessorKey: 'entryPage',
    header: 'Entry Page',
    size: COLUMN_SIZES.entryPage,
    cell: ({ row }) => {
      const entryPage = row.getValue('entryPage') as ViewData['entryPage']
      return (
        <EntryPageCell
          data={{
            title: entryPage.title,
            url: entryPage.url,
            hasQueryString: entryPage.hasQueryString,
            queryString: entryPage.queryString,
            utmCampaign: entryPage.utmCampaign,
          }}
          maxLength={35}
        />
      )
    },
    meta: {
      priority: 'secondary',
      mobileLabel: 'Entry',
    },
  },
  {
    accessorKey: 'totalViews',
    header: ({ column }) => <DataTableColumnHeaderSortable column={column} title="Views" className="text-right" />,
    size: COLUMN_SIZES.views,
    cell: ({ row }) => <NumericCell value={row.getValue('totalViews') as number} />,
    meta: {
      priority: 'primary',
      cardPosition: 'body',
      mobileLabel: 'Views',
    },
  },
]

// Transform API data to component interface
const transformViewData = (record: ViewRecord): ViewData => {
  const entryPageData = parseEntryPage(record.entry_page, record.entry_page_title)

  return {
    lastVisit: record.last_visit,
    visitorInfo: {
      country: {
        code: record.country_code?.toLowerCase() || '000',
        name: record.country_name || 'Unknown',
        region: record.region_name || '',
        city: record.city_name || '',
      },
      os: {
        icon: record.os_name?.toLowerCase().replace(/\s+/g, '_') || 'unknown',
        name: record.os_name || 'Unknown',
      },
      browser: {
        icon: record.browser_name?.toLowerCase().replace(/\s+/g, '_') || 'unknown',
        name: record.browser_name || 'Unknown',
        version: record.browser_version || '',
      },
      user: record.user_id && record.user_login
        ? {
            username: record.user_login,
            id: record.user_id,
            email: record.user_email || '',
            role: record.user_role || '',
          }
        : undefined,
      ipAddress: record.ip_address || undefined,
      hash: record.visitor_hash || undefined,
    },
    page: {
      title: record.entry_page_title || record.entry_page || 'Unknown',
      url: record.entry_page || '/',
    },
    referrer: {
      domain: record.referrer_domain || undefined,
      fullUrl: record.referrer_domain ? `https://${record.referrer_domain}` : undefined,
      category: record.referrer_channel?.toUpperCase() || 'DIRECT TRAFFIC',
    },
    entryPage: {
      title: entryPageData.title,
      url: record.entry_page || '/',
      hasQueryString: entryPageData.hasQueryString,
      queryString: entryPageData.queryString,
      utmCampaign: entryPageData.utmCampaign,
    },
    totalViews: record.total_views || 0,
  }
}

function RouteComponent() {
  const navigate = useNavigate()
  const { filters: urlFilters, page: urlPage } = routeApi.useSearch()

  const [sorting, setSorting] = useState<SortingState>([{ id: 'lastVisit', desc: true }])
  const lastSyncedFiltersRef = useRef<string | null>(null)
  // Default to last 30 days
  const [dateRange, setDateRange] = useState<DateRange>(() => {
    const today = new Date()
    const thirtyDaysAgo = new Date()
    thirtyDaysAgo.setDate(today.getDate() - 29)
    return {
      from: thirtyDaysAgo,
      to: today,
    }
  })
  const [compareDateRange, setCompareDateRange] = useState<DateRange | undefined>(undefined)

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

  // Get filter fields for 'views' group from localized data
  const filterFields = useMemo<FilterField[]>(() => {
    return wp.getFilterFieldsByGroup('views') as FilterField[]
  }, [wp])

  // Initialize filters state - null until URL sync is complete
  const [appliedFilters, setAppliedFilters] = useState<Filter[] | null>(null)

  // Initialize page state
  const [page, setPage] = useState(1)

  // Sync filters FROM URL on mount (only once)
  useEffect(() => {
    if (lastSyncedFiltersRef.current !== null) return // Already initialized

    // Initialize immediately - don't wait for filterFields
    // This prevents multiple re-renders when filterFields loads later
    const filtersFromUrl = urlFiltersToFilters(urlFilters, filterFields)

    setAppliedFilters(filtersFromUrl)
    if (urlPage && urlPage > 1) {
      setPage(urlPage)
    }

    // Mark as initialized with the current filter state
    lastSyncedFiltersRef.current = JSON.stringify(filtersToUrlFilters(filtersFromUrl))
  }, [urlFilters, urlPage, filterFields])

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

  const handleDateRangeUpdate = useCallback(
    (values: { range: DateRange; rangeCompare?: DateRange }) => {
      setDateRange(values.range)
      setCompareDateRange(values.rangeCompare)
      setPage(1)
    },
    []
  )

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
  } = useQuery({
    ...getViewsQueryOptions({
      page,
      per_page: PER_PAGE,
      order_by: orderBy,
      order: order as 'asc' | 'desc',
      date_from: formatDateForAPI(dateRange.from),
      date_to: formatDateForAPI(dateRange.to || dateRange.from),
      ...(compareDateRange?.from && compareDateRange?.to && {
        previous_date_from: formatDateForAPI(compareDateRange.from),
        previous_date_to: formatDateForAPI(compareDateRange.to),
      }),
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
    clearCachedColumns(CONTEXT)
  }, [arraysEqual])

  // Transform API data to component interface
  const tableData = useMemo(() => {
    if (!response?.data?.data?.rows) return []
    return response.data.data.rows.map(transformViewData)
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
      {/* Header row with title and filter button */}
      <div className="flex items-center justify-between px-4 py-3 bg-white border-b border-input">
        <h1 className="text-xl font-semibold text-neutral-800">{__('Views', 'wp-statistics')}</h1>
        <div className="flex items-center gap-3">
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

      <div className="p-2">
        {/* Applied filters row (separate from button) */}
        {appliedFilters && appliedFilters.length > 0 && (
          <FilterBar filters={appliedFilters} onRemoveFilter={handleRemoveFilter} className="mb-2" />
        )}

        {isError ? (
          <div className="p-2 text-center text-destructive">
            {__('Failed to load views data. Please try again.', 'wp-statistics')}
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
            emptyStateMessage={__('No views found for the selected period', 'wp-statistics')}
          />
        )}
      </div>
    </div>
  )
}
