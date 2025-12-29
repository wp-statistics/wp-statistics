import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import type { ColumnDef, SortingState, VisibilityState } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { useCallback, useEffect, useMemo, useRef, useState } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { DataTableColumnHeaderSortable } from '@/components/custom/data-table-column-header-sortable'
import {
  DurationCell,
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
import { formatReferrerChannel } from '@/lib/filter-utils'
import { parseEntryPage } from '@/lib/url-utils'
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

// Column configuration for this page
const COLUMN_CONFIG: ColumnConfig = {
  baseColumns: ['visitor_id', 'visitor_hash'],
  columnDependencies: {
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
  },
  context: CONTEXT,
}

// Default columns when no preferences are set (all columns visible)
const DEFAULT_API_COLUMNS = getDefaultApiColumns(COLUMN_CONFIG)

// Get cached column order from localStorage (same as visible columns order)
const getCachedColumnOrder = (): string[] => {
  return getCachedVisibleColumns(CONTEXT) || []
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

// Transform API response to component interface
const transformVisitorData = (apiVisitor: APIOnlineVisitor): OnlineVisitor => {
  const lastVisitDate = new Date(apiVisitor.last_visit)
  // Calculate online duration based on total_sessions (approximate)
  const onlineForSeconds = Math.max(0, apiVisitor.total_sessions * 60) // Rough estimate

  // Parse entry page data using shared utility
  const entryPageData = parseEntryPage(apiVisitor.entry_page)

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
    page: entryPageData.path,
    pageTitle: getPageTitle(entryPageData.path),
    totalViews: apiVisitor.total_views || 0,
    entryPage: entryPageData.path,
    entryPageTitle: getPageTitle(entryPageData.path),
    entryPageHasQuery: entryPageData.hasQueryString,
    entryPageQueryString: entryPageData.queryString,
    referrerDomain: apiVisitor.referrer_domain || undefined,
    referrerCategory: formatReferrerChannel(apiVisitor.referrer_channel),
    lastVisit: lastVisitDate,
  }
}

const createColumns = (config: VisitorInfoConfig): ColumnDef<OnlineVisitor>[] => [
  {
    accessorKey: 'visitorInfo',
    header: 'Visitor Info',
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
  },
  {
    accessorKey: 'onlineFor',
    header: ({ column }) => <DataTableColumnHeaderSortable column={column} title="Online" className="text-right" />,
    size: 75,
    cell: ({ row }) => <DurationCell seconds={row.original.onlineFor} />,
  },
  {
    accessorKey: 'page',
    header: 'Page',
    cell: ({ row }) => (
      <PageCell
        data={{ title: row.original.pageTitle, url: row.original.page }}
        maxLength={35}
      />
    ),
  },
  {
    accessorKey: 'totalViews',
    header: ({ column }) => (
      <DataTableColumnHeaderSortable column={column} title="Views" className="text-right" />
    ),
    size: 70,
    cell: ({ row }) => <NumericCell value={row.original.totalViews} />,
  },
  {
    accessorKey: 'entryPage',
    header: 'Entry Page',
    cell: ({ row }) => {
      const visitor = row.original
      return (
        <EntryPageCell
          data={{
            title: visitor.entryPageTitle,
            url: visitor.entryPage,
            hasQueryString: visitor.entryPageHasQuery,
            queryString: visitor.entryPageQueryString,
          }}
          maxLength={35}
        />
      )
    },
  },
  {
    accessorKey: 'referrer',
    header: 'Referrer',
    cell: ({ row }) => (
      <ReferrerCell
        data={{
          domain: row.original.referrerDomain,
          category: row.original.referrerCategory,
        }}
        maxLength={25}
      />
    ),
  },
  {
    accessorKey: 'lastVisit',
    header: ({ column }) => <DataTableColumnHeaderSortable column={column} title="Last Visit" />,
    cell: ({ row }) => <LastVisitCell date={row.original.lastVisit} />,
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
    // Use cached visibility from localStorage while waiting
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
