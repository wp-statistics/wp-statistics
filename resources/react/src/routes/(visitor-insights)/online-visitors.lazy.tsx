import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import type { ColumnDef } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { useCallback, useMemo, useState } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { DataTableColumnHeader } from '@/components/custom/data-table-column-header'
import { ErrorMessage } from '@/components/custom/error-message'
import { NoticeContainer } from '@/components/ui/notice-container'
import { PanelSkeleton, TableSkeleton } from '@/components/ui/skeletons'
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
import { useDataTablePreferences } from '@/hooks/use-data-table-preferences'
import { useUrlSortSync } from '@/hooks/use-url-sort-sync'
import { COLUMN_SIZES } from '@/lib/column-sizes'
import { type ColumnConfig, getDefaultApiColumns } from '@/lib/column-utils'
import { formatReferrerChannel } from '@/lib/filter-utils'
import { parseEntryPage } from '@/lib/url-utils'
import { WordPress } from '@/lib/wordpress'
import type { OnlineVisitor as APIOnlineVisitor } from '@/services/visitor-insight/get-online-visitors'
import { getOnlineVisitorsQueryOptions } from '@/services/visitor-insight/get-online-visitors'

const CONTEXT = 'online_visitors_data_table'
const DEFAULT_HIDDEN_COLUMNS: string[] = ['entryPage', 'lastVisit']

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

export const Route = createLazyFileRoute('/(visitor-insights)/online-visitors')({
  component: RouteComponent,
})

interface OnlineVisitor {
  id: string
  country: string
  countryCode: string
  region: string
  city: string
  os: string
  osName: string
  browser: string
  browserName: string
  browserVersion: string
  userId?: string
  username?: string
  email?: string
  userRole?: string
  ipAddress?: string
  hash?: string
  onlineFor: number
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
  const onlineForSeconds = Math.max(0, apiVisitor.total_sessions * 60)
  const entryPageData = parseEntryPage(apiVisitor.entry_page)

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
    header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} title="Visitor Info" />,
    enableSorting: false,
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
            user:
              visitor.userId && visitor.username
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
    accessorKey: 'page',
    header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} title="Page" />,
    enableSorting: false,
    cell: ({ row }) => <PageCell data={{ title: row.original.pageTitle, url: row.original.page }} maxLength={35} />,
    meta: {
      priority: 'primary',
      cardPosition: 'header',
      mobileLabel: 'Page',
    },
  },
  {
    accessorKey: 'onlineFor',
    header: ({ column, table }) => (
      <DataTableColumnHeader column={column} table={table} title="Online" className="text-right" />
    ),
    size: COLUMN_SIZES.onlineFor,
    cell: ({ row }) => <DurationCell seconds={row.original.onlineFor} />,
    meta: {
      priority: 'primary',
      cardPosition: 'body',
      mobileLabel: 'Online',
    },
  },
  {
    accessorKey: 'totalViews',
    header: ({ column, table }) => (
      <DataTableColumnHeader column={column} table={table} title="Views" className="text-right" />
    ),
    size: COLUMN_SIZES.views,
    cell: ({ row }) => <NumericCell value={row.original.totalViews} />,
    meta: {
      priority: 'primary',
      cardPosition: 'body',
      mobileLabel: 'Views',
    },
  },
  {
    accessorKey: 'referrer',
    header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} title="Referrer" />,
    enableSorting: false,
    cell: ({ row }) => (
      <ReferrerCell
        data={{
          domain: row.original.referrerDomain,
          category: row.original.referrerCategory,
        }}
        maxLength={25}
      />
    ),
    meta: {
      priority: 'secondary',
      mobileLabel: 'Referrer',
    },
  },
  // Hidden by default
  {
    accessorKey: 'entryPage',
    header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} title="Entry Page" />,
    enableSorting: false,
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
    meta: {
      priority: 'secondary',
      mobileLabel: 'Entry',
    },
  },
  {
    accessorKey: 'lastVisit',
    header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} title="Last Visit" />,
    cell: ({ row }) => <LastVisitCell date={row.original.lastVisit} />,
    meta: {
      priority: 'secondary',
      mobileLabel: 'Last Visit',
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

  const [page, setPage] = useState(1)

  const { sorting, handleSortingChange, orderBy, order } = useUrlSortSync({
    defaultSort: [{ id: 'lastVisit', desc: true }],
    onPageReset: () => setPage(1),
  })

  // Fetch data from API
  const {
    data: response,
    isLoading,
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
      columns: DEFAULT_API_COLUMNS,
    }),
    placeholderData: keepPreviousData,
  })

  // Use the preferences hook for column management
  const {
    columnOrder,
    initialColumnVisibility,
    handleColumnVisibilityChange,
    handleColumnOrderChange,
    handleColumnPreferencesReset,
  } = useDataTablePreferences({
    context: CONTEXT,
    columns,
    defaultHiddenColumns: DEFAULT_HIDDEN_COLUMNS,
    defaultApiColumns: DEFAULT_API_COLUMNS,
    columnConfig: COLUMN_CONFIG,
    sorting,
    defaultSortColumn: 'lastVisit',
    preferencesFromApi: response?.data?.meta?.preferences?.columns,
    hasApiResponse: !!response?.data,
  })

  // Transform API data to component format
  const visitors = response?.data?.data?.rows?.map(transformVisitorData) || []
  const total = response?.data?.meta?.total_rows ?? 0
  const totalPages = response?.data?.meta?.total_pages || Math.ceil(total / PER_PAGE) || 1

  const handlePageChange = useCallback(
    (newPage: number) => {
      setPage(newPage)
    },
    [setPage]
  )

  const showSkeleton = isLoading && !response

  return (
    <div className="min-w-0">
      <div className="flex items-center justify-between px-4 py-3 ">
        <h1 className="text-2xl font-semibold text-neutral-800">{__('Online Visitors', 'wp-statistics')}</h1>
      </div>

      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="online-visitors" />
        {isError ? (
          <div className="p-2 text-center">
            <ErrorMessage message={__('Failed to load online visitors', 'wp-statistics')} />
            <p className="text-sm text-muted-foreground">{error?.message}</p>
          </div>
        ) : showSkeleton ? (
          <PanelSkeleton titleWidth="w-32">
            <TableSkeleton rows={10} columns={7} />
          </PanelSkeleton>
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
            stickyHeader={true}
            borderless
          />
        )}
      </div>
    </div>
  )
}
